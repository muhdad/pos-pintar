import Dexie from 'dexie';

/**
 * Inisialisasi Database Lokal IndexedDB (Dexie.js)
 * Memastikan kasir tetap beroperasi 100% saat jaringan internet offline.
 */
export const posDb = new Dexie('POS_PINTAR_DB');

posDb.version(1).stores({
    products: 'id, tenant_id, category_id, name, barcode',
    categories: 'id, name',
    stocks: 'raw_material_id, name',
    orders: 'id, tenant_id, outlet_id, order_number, sync_status, created_at',
    sync_queue: '++id, entity_type, created_at, retry_count',
});

/**
 * Alpine.js Store & Controller untuk Terminal Kasir
 */
export function posTerminal() {
    return {
        // State
        isOnline: navigator.onLine,
        isSyncing: false,
        isProcessingCheckout: false,
        successModalOpen: false,
        completedOrder: null,
        activeCategory: 'ALL',
        searchQuery: '',
        categories: [],
        products: [],
        cart: [],
        discount: 0,
        taxPercent: 11, // PPN 11%
        orderType: 'DINE_IN', // DINE_IN, TAKE_AWAY, GOFOOD, GRABFOOD, SHOPEEFOOD
        paymentModalOpen: false,
        mobileCartOpen: false,
        selectedPaymentMethod: 'CASH',
        cashGiven: 0,
        customerName: '',
        customerPhone: '',
        pinModalOpen: false,
        cashierPin: '',
        activeCashier: {
            name: 'Kasir',
            role: 'CASHIER'
        },

        // Inisialisasi Terminal
        async init() {
            // Keyboard shortcuts untuk kasir
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    if (this.cart.length > 0 && !this.paymentModalOpen) {
                        this.paymentModalOpen = true;
                        this.cashGiven = this.finalTotal;
                    }
                }
                if (e.key === 'F3' || (e.key === '/' && document.activeElement.tagName !== 'INPUT')) {
                    e.preventDefault();
                    const searchInput = document.getElementById('pos-search-input');
                    if (searchInput) searchInput.focus();
                }
                if (e.key === 'Escape') {
                    if (this.paymentModalOpen) this.paymentModalOpen = false;
                    if (this.successModalOpen) this.successModalOpen = false;
                }
            });

            // Listener status koneksi internet
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.syncOfflineQueue();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            // 1. Jika blade telah mem-passing produk awal, gunakan langsung seketika (0ms)
            if (window.POS_INITIAL_PRODUCTS && window.POS_INITIAL_PRODUCTS.length > 0) {
                this.products = window.POS_INITIAL_PRODUCTS;
                this.categories = window.POS_INITIAL_CATEGORIES || [];
                // Simpan ke IndexedDB lokal di background untuk offline berikutnya
                try {
                    await posDb.products.bulkPut(window.POS_INITIAL_PRODUCTS);
                    if (window.POS_INITIAL_CATEGORIES && window.POS_INITIAL_CATEGORIES.length > 0) {
                        await posDb.categories.bulkPut(window.POS_INITIAL_CATEGORIES);
                    }
                } catch (e) {
                    console.warn('Gagal cache ke IndexedDB:', e);
                }
            } else {
                // Muat data katalog lokal dari Dexie
                await this.loadLocalCatalog();
            }

            // Coba ambil pembaruan katalog jika online
            if (this.isOnline) {
                await this.refreshCatalogFromCloud();
                await this.syncOfflineQueue();
            }

            // Sync otomatis setiap 30 detik jika ada antrean
            setInterval(() => {
                if (this.isOnline && !this.isSyncing) {
                    this.syncOfflineQueue();
                }
            }, 30000);
        },

        // Muat dari IndexedDB
        async loadLocalCatalog() {
            this.categories = await posDb.categories.toArray();
            this.products = await posDb.products.toArray();
        },

        // Ambil update master data dari endpoint Laravel
        async refreshCatalogFromCloud() {
            try {
                const tenantId = window.POS_CONFIG?.tenantId || '';
                const outletId = window.POS_CONFIG?.outletId || '';
                const url = `/api/pos/bootstrap-data?tenant_id=${tenantId}&outlet_id=${outletId}`;

                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.categories && data.categories.length > 0) {
                        await posDb.categories.clear();
                        await posDb.categories.bulkPut(data.categories);
                        this.categories = data.categories;
                    }

                    if (data.products && data.products.length > 0) {
                        await posDb.products.clear();
                        await posDb.products.bulkPut(data.products);
                        this.products = data.products;
                    }

                    if (data.stocks && data.stocks.length > 0) {
                        await posDb.stocks.clear();
                        await posDb.stocks.bulkPut(data.stocks);
                    }
                }
            } catch (err) {
                console.warn('Gagal memuat katalog cloud, beralih ke mode offline lokal.', err);
            }
        },

        // Filter produk berdasarkan kategori dan pencarian
        get filteredProducts() {
            return this.products.filter(product => {
                const matchCategory = this.activeCategory === 'ALL' || product.category_id === this.activeCategory;
                const matchSearch = !this.searchQuery || 
                    product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    (product.barcode && product.barcode.includes(this.searchQuery));
                return matchCategory && matchSearch;
            });
        },

        // Tambah item ke keranjang (0ms latency, client-side)
        addToCart(product) {
            const existingIndex = this.cart.findIndex(item => item.product_id === product.id);
            if (existingIndex > -1) {
                this.cart[existingIndex].quantity += 1;
                this.cart[existingIndex].subtotal = this.cart[existingIndex].quantity * this.cart[existingIndex].unit_price;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    unit_price: parseFloat(product.selling_price),
                    cogs: parseFloat(product.cogs || 0),
                    quantity: 1,
                    subtotal: parseFloat(product.selling_price),
                    notes: '',
                    recipes: product.recipes || []
                });
            }
        },

        // Ubah jumlah item
        updateQuantity(index, delta) {
            this.cart[index].quantity += delta;
            if (this.cart[index].quantity <= 0) {
                this.cart.splice(index, 1);
            } else {
                this.cart[index].subtotal = this.cart[index].quantity * this.cart[index].unit_price;
            }
        },

        // Perhitungan total
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
        },

        get taxAmount() {
            const taxable = Math.max(0, this.subtotal - this.discount);
            return Math.round(taxable * (this.taxPercent / 100));
        },

        get finalTotal() {
            return Math.max(0, this.subtotal - this.discount + this.taxAmount);
        },

        get changeDue() {
            if (this.selectedPaymentMethod === 'CASH') {
                return Math.max(0, this.cashGiven - this.finalTotal);
            }
            return 0;
        },

        // Selesaikan pesanan (Checkout Offline-First & Direct Cloud Sync)
        async processCheckout() {
            if (this.cart.length === 0 || this.isProcessingCheckout) return;
            this.isProcessingCheckout = true;

            const orderId = crypto.randomUUID();
            const now = new Date().toISOString();
            const orderNumber = `POS-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Math.random().toString(36).substring(2, 6).toUpperCase()}`;

            const orderPayload = {
                id: orderId,
                tenant_id: window.POS_CONFIG?.tenantId || null,
                outlet_id: window.POS_CONFIG?.outletId || null,
                shift_id: window.POS_CONFIG?.shiftId || null,
                user_id: window.POS_CONFIG?.userId || null,
                order_number: orderNumber,
                status: 'PAID',
                total_amount: this.subtotal,
                discount_amount: this.discount,
                tax_amount: this.taxAmount,
                final_amount: this.finalTotal,
                customer_name: this.customerName ? (this.customerName + ' [' + this.orderType + ']') : ('[' + this.orderType + ']'),
                customer_phone: this.customerPhone || null,
                items: this.cart.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    unit_cost: item.cogs,
                    subtotal: item.subtotal,
                    notes: item.notes || null,
                })),
                payment: {
                    method: this.selectedPaymentMethod,
                    amount: this.finalTotal,
                    status: 'SETTLED',
                    paid_at: now
                },
                sync_status: 'PENDING_SYNC',
                created_at: now,
            };

            // 1. Simpan ke IndexedDB lokal
            await posDb.orders.put(orderPayload);
            await posDb.sync_queue.add({
                entity_type: 'ORDER',
                payload: orderPayload,
                created_at: Date.now(),
                retry_count: 0
            });

            // 2. Potong stok lokal secara optimis (BOM Recipe)
            for (const item of this.cart) {
                if (item.recipes && item.recipes.length > 0) {
                    for (const recipe of item.recipes) {
                        const localStock = await posDb.stocks.get(recipe.raw_material_id);
                        if (localStock) {
                            localStock.current_stock -= (item.quantity * parseFloat(recipe.quantity));
                            await posDb.stocks.put(localStock);
                        }
                    }
                }
            }

            // 3. Picu cetak struk jika modul thermal printer siap
            if (window.thermalPrinter && window.thermalPrinter.isConnected) {
                window.thermalPrinter.printReceipt(orderPayload);
            }

            // 4. Sinkronisasikan secara sinkron (await) ke cloud backend saat online
            if (this.isOnline) {
                await this.syncOfflineQueue();
            }

            // 5. Tampilkan Modal Sukses Transaksi
            this.completedOrder = {
                ...orderPayload,
                changeDue: this.changeDue,
                cashGiven: this.cashGiven
            };

            // Bersihkan form & cart
            this.cart = [];
            this.discount = 0;
            this.cashGiven = 0;
            this.customerName = '';
            this.customerPhone = '';
            this.paymentModalOpen = false;
            this.mobileCartOpen = false;
            this.isProcessingCheckout = false;
            this.successModalOpen = true;
        },

        // Sinkronisasi antrean lokal ke API Laravel
        async syncOfflineQueue() {
            if (this.isSyncing) return;
            this.isSyncing = true;

            try {
                const queuedItems = await posDb.sync_queue.toArray();
                if (queuedItems.length === 0) {
                    this.isSyncing = false;
                    return;
                }

                const ordersToSync = queuedItems
                    .filter(item => item.entity_type === 'ORDER')
                    .map(item => item.payload);

                if (ordersToSync.length > 0) {
                    const response = await fetch('/api/pos/sync', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ orders: ordersToSync })
                    });

                    if (response.ok) {
                        const res = await response.json();
                        if (res.synced_ids && res.synced_ids.length > 0) {
                            // Tandai order sebagai SYNCED di lokal dan hapus dari antrean
                            for (const id of res.synced_ids) {
                                const localOrder = await posDb.orders.get(id);
                                if (localOrder) {
                                    localOrder.sync_status = 'SYNCED';
                                    await posDb.orders.put(localOrder);
                                }
                            }

                            const idsToRemove = queuedItems
                                .filter(item => res.synced_ids.includes(item.payload.id))
                                .map(item => item.id);

                            await posDb.sync_queue.bulkDelete(idsToRemove);
                        }
                    }
                }
            } catch (err) {
                console.warn('Gagal menyinkronkan antrean offline ke server:', err);
            } finally {
                this.isSyncing = false;
            }
        }
    };
}
