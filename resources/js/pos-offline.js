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
 * Format string ribuan dengan titik (contoh: 25000 -> 25.000)
 */
export function formatThousand(val) {
    if (val === null || val === undefined || val === '') return '';
    const num = typeof val === 'number' ? val : parseInt(String(val).replace(/\D/g, ''), 10);
    if (isNaN(num)) return '';
    return num.toLocaleString('id-ID');
}

/**
 * Parse string dengan format titik ribuan menjadi integer (contoh: "25.000" -> 25000)
 */
export function parseThousand(str) {
    if (!str) return 0;
    const clean = String(str).replace(/\D/g, '');
    return clean ? parseInt(clean, 10) : 0;
}

/**
 * Alpine.js Store & Controller untuk Terminal Kasir
 */
export function posTerminal() {
    return {
        // Status Konektivitas & Sinkronisasi
        isOnline: navigator.onLine,
        isSyncing: false,
        isProcessingCheckout: false,
        isHoldingOrder: false,

        // Modal Views
        paymentModalOpen: false,
        successModalOpen: false,
        mobileCartOpen: false,
        openBillsModalOpen: false,
        startShiftModalOpen: false,
        pettyCashModalOpen: false,
        closeShiftModalOpen: false,
        shiftSummaryModalOpen: false,

        // Data Master
        activeCategory: 'ALL',
        searchQuery: '',
        categories: [],
        products: [],
        stocks: [],
        tables: [],
        openBills: [],

        // State Transaksi & Keranjang
        cart: [],
        discount: 0,
        taxPercent: 11, // PPN 11%
        orderType: 'DINE_IN', // DINE_IN, TAKE_AWAY, GOFOOD, GRABFOOD, SHOPEEFOOD
        selectedTableId: '',
        selectedTableName: '',
        currentOpenBillId: null,
        customerName: '',
        customerPhone: '',
        completedOrder: null,

        // Pembayaran
        selectedPaymentMethod: 'CASH', // CASH, QRIS, TRANSFER, MULTI
        cashGiven: 0,
        multiCashAmount: 0,
        multiNonCashAmount: 0,
        multiNonCashMethod: 'QRIS',

        // Shift Kasir
        activeShift: null,
        startCash: 0,
        startCashNotes: '',
        pettyCashAmount: 0,
        pettyCashReason: '',
        actualCash: 0,
        closeShiftNotes: '',
        shiftReport: null,

        // Inisialisasi Terminal
        async init() {
            // Setup data shift awal
            this.activeShift = window.POS_CONFIG?.activeShift || null;
            this.tables = window.POS_INITIAL_TABLES || [];
            this.openBills = window.POS_INITIAL_OPEN_BILLS || [];
            this.stocks = window.POS_INITIAL_STOCKS || [];

            // Keyboard shortcuts untuk kasir
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    if (this.cart.length > 0 && !this.paymentModalOpen) {
                        this.openPaymentModal();
                    }
                }
                if (e.key === 'F3' || (e.key === '/' && document.activeElement.tagName !== 'INPUT')) {
                    e.preventDefault();
                    const searchInput = document.getElementById('pos-search-input');
                    if (searchInput) searchInput.focus();
                }
                if (e.key === 'F4') {
                    e.preventDefault();
                    if (this.cart.length > 0) {
                        this.holdCurrentOrder();
                    }
                }
                if (e.key === 'Escape') {
                    this.paymentModalOpen = false;
                    this.successModalOpen = false;
                    this.openBillsModalOpen = false;
                    this.pettyCashModalOpen = false;
                }
            });

            // Listener status koneksi internet
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.syncOfflineQueue();
                this.fetchOpenBills();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            // Inisialisasi katalog
            if (window.POS_INITIAL_PRODUCTS !== undefined) {
                this.products = window.POS_INITIAL_PRODUCTS || [];
                this.categories = window.POS_INITIAL_CATEGORIES || [];
                try {
                    await posDb.products.clear();
                    if (this.products.length > 0) {
                        await posDb.products.bulkPut(this.products);
                    }
                    if (this.categories.length > 0) {
                        await posDb.categories.clear();
                        await posDb.categories.bulkPut(this.categories);
                    }
                } catch (e) {
                    console.warn('Gagal sinkronisasi cache ke IndexedDB:', e);
                }
            } else {
                await this.loadLocalCatalog();
            }

            if (this.isOnline) {
                await this.refreshCatalogFromCloud();
                await this.syncOfflineQueue();
            }

            // Sync otomatis background
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
            this.stocks = await posDb.stocks.toArray();
        },

        // Ambil update master data dari server
        async refreshCatalogFromCloud() {
            try {
                const tenantId = window.POS_CONFIG?.tenantId || '';
                const outletId = window.POS_CONFIG?.outletId || '';
                const url = `/api/pos/bootstrap-data?tenant_id=${tenantId}&outlet_id=${outletId}`;

                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.categories?.length > 0) {
                        await posDb.categories.clear();
                        await posDb.categories.bulkPut(data.categories);
                        this.categories = data.categories;
                    }

                    if (data.products?.length > 0) {
                        await posDb.products.clear();
                        await posDb.products.bulkPut(data.products);
                        this.products = data.products;
                    }

                    if (data.stocks?.length > 0) {
                        await posDb.stocks.clear();
                        await posDb.stocks.bulkPut(data.stocks);
                        this.stocks = data.stocks;
                    }

                    if (data.tables?.length > 0) {
                        this.tables = data.tables;
                    }

                    if (data.active_shift) {
                        this.activeShift = data.active_shift;
                    }
                }
            } catch (err) {
                console.warn('Gagal memuat katalog cloud, beralih ke mode offline lokal.', err);
            }
        },

        // Pengaturan Multi-Pricing Ojol (+20% otomatis untuk Grab, GoFood, ShopeeFood)
        get priceMultiplier() {
            if (this.orderType === 'GOFOOD' || this.orderType === 'GRABFOOD' || this.orderType === 'SHOPEEFOOD') {
                return 1.20; // Markup 20%
            }
            return 1.0;
        },

        // Hitung harga dinamis per produk sesuai saluran order
        getProductPrice(product) {
            const basePrice = parseFloat(product.selling_price) || 0;
            if (this.priceMultiplier === 1.0) return basePrice;
            // Dibulatkan ke kelipatan 500 rupiah terdekat agar praktis
            return Math.round((basePrice * this.priceMultiplier) / 500) * 500;
        },

        // Ganti tipe order (Dine In, Takeaway, Ojol) & sesuaikan harga cart
        setOrderType(type) {
            this.orderType = type;
            // Jika beralih dari dine in, kosongkan meja
            if (type !== 'DINE_IN') {
                this.selectedTableId = '';
                this.selectedTableName = '';
            }
            // Recalculate harga tiap item di keranjang
            this.cart.forEach(item => {
                const prod = this.products.find(p => p.id === item.product_id);
                if (prod) {
                    item.unit_price = this.getProductPrice(prod);
                    item.subtotal = item.unit_price * item.quantity;
                }
            });
        },

        // Pilih Meja
        selectTable(table) {
            this.selectedTableId = table.id;
            this.selectedTableName = table.name;
        },

        // Filter produk berdasarkan kategori, pencarian & stok
        get filteredProducts() {
            return this.products.filter(product => {
                const matchCategory = this.activeCategory === 'ALL' || product.category_id === this.activeCategory;
                const matchSearch = !this.searchQuery || 
                    product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    (product.barcode && product.barcode.includes(this.searchQuery));
                return matchCategory && matchSearch;
            });
        },

        // Cek ketersediaan stok
        isProductOutOfStock(product) {
            // Jika produk dinonaktifkan dari backoffice
            if (product.is_active === false) return true;

            // Jika tracking stok dinonaktifkan (unlimited)
            if (!product.track_stock) return false;

            // Jika ada field current_stock eksplisit (misal barang retail botol/kaleng)
            if (product.current_stock !== undefined && product.current_stock !== null) {
                return Number(product.current_stock) <= 0;
            }

            // Jika produk memiliki resep bahan baku (BOM) & gudang memiliki data stok
            if (product.recipes && product.recipes.length > 0 && this.stocks && this.stocks.length > 0) {
                for (const recipe of product.recipes) {
                    const st = this.stocks.find(s => s.raw_material_id === recipe.raw_material_id);
                    // Jika bahan baku terdaftar di resep dan stoknya habis / kurang dari takaran
                    if (st && Number(st.current_stock) < Number(recipe.quantity)) {
                        return true;
                    }
                }
            }

            // Default: menu siap dijual di kasir
            return false;
        },

        // Alasan stok habis untuk info kasir
        getOutOfStockReason(product) {
            if (product.is_active === false) return 'Menu sedang dinonaktifkan di Backoffice';
            if (product.recipes && product.recipes.length > 0 && this.stocks && this.stocks.length > 0) {
                for (const recipe of product.recipes) {
                    const st = this.stocks.find(s => s.raw_material_id === recipe.raw_material_id);
                    if (st && Number(st.current_stock) < Number(recipe.quantity)) {
                        const matName = recipe.raw_material?.name || st.name || 'Bahan Baku';
                        return `Stok [${matName}] di gudang habis (sisa: ${Number(st.current_stock)})`;
                    }
                }
            }
            return 'Stok gudang belum di-restock';
        },

        // Tambah item ke keranjang (0ms latency, client-side)
        addToCart(product) {
            if (this.isProductOutOfStock(product)) {
                const reason = this.getOutOfStockReason(product);
                const bypass = confirm(`Menu "${product.name}" terdeteksi HABIS:\n• ${reason}\n\nApakah kasir ingin tetap menambahkan menu ini ke pesanan?`);
                if (!bypass) {
                    return;
                }
            }

            const currentPrice = this.getProductPrice(product);
            const existingIndex = this.cart.findIndex(item => item.product_id === product.id);

            if (existingIndex > -1) {
                this.cart[existingIndex].quantity += 1;
                this.cart[existingIndex].subtotal = this.cart[existingIndex].quantity * this.cart[existingIndex].unit_price;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    unit_price: currentPrice,
                    cogs: parseFloat(product.cogs || 0),
                    quantity: 1,
                    subtotal: currentPrice,
                    notes: '',
                    recipes: product.recipes || []
                });
            }
        },

        // Ubah kuantitas item
        updateQuantity(index, delta) {
            this.cart[index].quantity += delta;
            if (this.cart[index].quantity <= 0) {
                this.cart.splice(index, 1);
            } else {
                this.cart[index].subtotal = this.cart[index].quantity * this.cart[index].unit_price;
            }
        },

        // Perhitungan subtotal, PPN, dan grand total
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

        // Perhitungan kembalian tunai & multi-payment
        get changeDue() {
            if (this.selectedPaymentMethod === 'CASH') {
                return Math.max(0, this.cashGiven - this.finalTotal);
            } else if (this.selectedPaymentMethod === 'MULTI') {
                const totalPaid = (Number(this.multiCashAmount) || 0) + (Number(this.multiNonCashAmount) || 0);
                return Math.max(0, totalPaid - this.finalTotal);
            }
            return 0;
        },

        get totalMultiPaid() {
            return (Number(this.multiCashAmount) || 0) + (Number(this.multiNonCashAmount) || 0);
        },

        get remainingMultiDue() {
            return Math.max(0, this.finalTotal - this.totalMultiPaid);
        },

        // Format angka titik ribuan untuk input
        get formattedCashGiven() {
            return formatThousand(this.cashGiven);
        },
        setFormattedCashGiven(val) {
            this.cashGiven = parseThousand(val);
        },

        get formattedMultiCash() {
            return formatThousand(this.multiCashAmount);
        },
        setFormattedMultiCash(val) {
            this.multiCashAmount = parseThousand(val);
        },

        get formattedMultiNonCash() {
            return formatThousand(this.multiNonCashAmount);
        },
        setFormattedMultiNonCash(val) {
            this.multiNonCashAmount = parseThousand(val);
        },

        get formattedStartCash() {
            return formatThousand(this.startCash);
        },
        setFormattedStartCash(val) {
            this.startCash = parseThousand(val);
        },

        get formattedPettyCash() {
            return formatThousand(this.pettyCashAmount);
        },
        setFormattedPettyCash(val) {
            this.pettyCashAmount = parseThousand(val);
        },

        get formattedActualCash() {
            return formatThousand(this.actualCash);
        },
        setFormattedActualCash(val) {
            this.actualCash = parseThousand(val);
        },

        // Buka modal bayar
        openPaymentModal() {
            this.paymentModalOpen = true;
            this.cashGiven = this.finalTotal;
            this.multiCashAmount = 0;
            this.multiNonCashAmount = this.finalTotal;
        },

        // ----------------------------------------------------
        // OPEN BILL (HOLD ORDER & RESUME)
        // ----------------------------------------------------

        // Simpan pesanan ke daftar Open Bill (Makan Dulu Bayar Nanti)
        async holdCurrentOrder() {
            if (this.cart.length === 0 || this.isHoldingOrder) return;
            this.isHoldingOrder = true;

            const orderId = this.currentOpenBillId || crypto.randomUUID();
            const orderNumber = `HOLD-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Math.random().toString(36).substring(2, 6).toUpperCase()}`;

            const labelPelanggan = this.orderType === 'DINE_IN' 
                ? (this.selectedTableName || (this.customerName || 'Dine-In')) 
                : (this.customerName || this.orderType);

            const payload = {
                id: orderId,
                tenant_id: window.POS_CONFIG?.tenantId || null,
                outlet_id: window.POS_CONFIG?.outletId || null,
                table_id: this.orderType === 'DINE_IN' ? this.selectedTableId || null : null,
                customer_name: labelPelanggan + ' [' + this.orderType + ']',
                discount_amount: this.discount,
                tax_amount: this.taxAmount,
                items: this.cart.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    unit_cost: item.cogs,
                    subtotal: item.subtotal,
                    notes: item.notes || null,
                }))
            };

            try {
                if (this.isOnline) {
                    const res = await fetch('/api/pos/hold-order', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(payload)
                    });

                    if (res.ok) {
                        const data = await res.json();
                        // Update status meja jika dine in
                        if (payload.table_id) {
                            const tbl = this.tables.find(t => t.id === payload.table_id);
                            if (tbl) tbl.is_occupied = true;
                        }
                        await this.fetchOpenBills();
                    }
                }

                // Cetak tiket dapur (KOT) agar koki segera memasak
                this.printKitchenTicket({
                    order_number: orderNumber,
                    table_name: this.selectedTableName || labelPelanggan,
                    order_type: this.orderType,
                    items: this.cart,
                    created_at: new Date()
                });

                // Reset keranjang untuk melayani antrean berikutnya
                this.cart = [];
                this.currentOpenBillId = null;
                this.customerName = '';
                this.selectedTableId = '';
                this.selectedTableName = '';
                this.mobileCartOpen = false;

                alert(`Pesanan ${labelPelanggan} berhasil disimpan sebagai Open Bill!`);
            } catch (e) {
                console.error('Gagal hold order:', e);
                alert('Gagal menyimpan pesanan: ' + e.message);
            } finally {
                this.isHoldingOrder = false;
            }
        },

        // Ambil daftar Open Bill aktif dari server
        async fetchOpenBills() {
            try {
                const outletId = window.POS_CONFIG?.outletId || '';
                const res = await fetch(`/api/pos/open-bills?outlet_id=${outletId}`);
                if (res.ok) {
                    const data = await res.json();
                    this.openBills = data.data || [];
                }
            } catch (e) {
                console.warn('Gagal fetch open bills:', e);
            }
        },

        // Panggil kembali tagihan Open Bill ke keranjang kasir
        resumeOpenBill(bill) {
            this.currentOpenBillId = bill.id;
            this.selectedTableId = bill.table_id || '';
            this.selectedTableName = bill.table?.name || '';
            this.customerName = bill.customer_name || '';
            this.discount = parseFloat(bill.discount_amount || 0);

            // Tentukan tipe order
            if (bill.customer_name?.includes('GOFOOD')) this.orderType = 'GOFOOD';
            else if (bill.customer_name?.includes('GRABFOOD')) this.orderType = 'GRABFOOD';
            else if (bill.customer_name?.includes('SHOPEEFOOD')) this.orderType = 'SHOPEEFOOD';
            else if (bill.customer_name?.includes('TAKE_AWAY') || bill.customer_name?.includes('Bungkus')) this.orderType = 'TAKE_AWAY';
            else this.orderType = 'DINE_IN';

            // Masukkan item ke keranjang
            this.cart = (bill.items || []).map(item => ({
                product_id: item.product_id,
                name: item.product?.name || item.name || 'Menu',
                unit_price: parseFloat(item.unit_price),
                cogs: parseFloat(item.unit_cost || 0),
                quantity: parseInt(item.quantity, 10),
                subtotal: parseFloat(item.subtotal),
                notes: item.notes || '',
                recipes: item.product?.recipes || []
            }));

            this.openBillsModalOpen = false;
        },

        // ----------------------------------------------------
        // CHECKOUT & MULTI-PAYMENT
        // ----------------------------------------------------

        async processCheckout() {
            if (this.cart.length === 0 || this.isProcessingCheckout) return;

            // Validasi kecukupan pembayaran
            if (this.selectedPaymentMethod === 'CASH' && this.cashGiven < this.finalTotal) {
                alert('Uang tunai yang diterima kurang dari total tagihan!');
                return;
            }
            if (this.selectedPaymentMethod === 'MULTI' && this.totalMultiPaid < this.finalTotal) {
                alert('Total kombinasi pembayaran (Tunai + Non-Tunai) kurang dari total tagihan!');
                return;
            }

            this.isProcessingCheckout = true;
            const orderId = this.currentOpenBillId || crypto.randomUUID();
            const now = new Date().toISOString();
            const orderNumber = `POS-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Math.random().toString(36).substring(2, 6).toUpperCase()}`;

            const labelPelanggan = this.orderType === 'DINE_IN' 
                ? (this.selectedTableName || (this.customerName || 'Dine-In')) 
                : (this.customerName || this.orderType);

            // Susun payload pembayaran tunggal atau multi-payment
            let paymentPayload = null;
            let paymentsPayload = null;

            if (this.selectedPaymentMethod === 'MULTI') {
                paymentsPayload = [
                    {
                        method: 'CASH',
                        amount: parseFloat(this.multiCashAmount),
                        status: 'SETTLED',
                        paid_at: now
                    },
                    {
                        method: this.multiNonCashMethod,
                        amount: parseFloat(this.multiNonCashAmount),
                        status: 'SETTLED',
                        paid_at: now
                    }
                ];
            } else {
                paymentPayload = {
                    method: this.selectedPaymentMethod,
                    amount: this.finalTotal,
                    status: 'SETTLED',
                    paid_at: now
                };
            }

            const orderPayload = {
                id: orderId,
                tenant_id: window.POS_CONFIG?.tenantId || null,
                outlet_id: window.POS_CONFIG?.outletId || null,
                shift_id: this.activeShift?.id || window.POS_CONFIG?.shiftId || null,
                user_id: window.POS_CONFIG?.userId || null,
                table_id: this.orderType === 'DINE_IN' ? this.selectedTableId || null : null,
                order_number: orderNumber,
                status: 'PAID',
                total_amount: this.subtotal,
                discount_amount: this.discount,
                tax_amount: this.taxAmount,
                final_amount: this.finalTotal,
                customer_name: labelPelanggan + ' [' + this.orderType + ']',
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
                payment: paymentPayload,
                payments: paymentsPayload,
                sync_status: 'PENDING_SYNC',
                created_at: now,
            };

            try {
                // 1. Simpan ke IndexedDB lokal (Offline-First)
                await posDb.orders.put(orderPayload);
                await posDb.sync_queue.add({
                    entity_type: 'ORDER',
                    payload: orderPayload,
                    created_at: Date.now(),
                    retry_count: 0
                });

                // 2. Jika merupakan pelunasan Open Bill dan online, panggil settle API
                if (this.currentOpenBillId && this.isOnline) {
                    await fetch(`/api/pos/open-bills/${this.currentOpenBillId}/settle`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            payment: paymentPayload,
                            payments: paymentsPayload
                        })
                    });
                    await this.fetchOpenBills();
                }

                // 3. Potong stok bahan baku lokal secara optimis (BOM)
                for (const item of this.cart) {
                    if (item.recipes?.length > 0) {
                        for (const recipe of item.recipes) {
                            const localStock = await posDb.stocks.get(recipe.raw_material_id);
                            if (localStock) {
                                localStock.current_stock -= (item.quantity * parseFloat(recipe.quantity));
                                await posDb.stocks.put(localStock);
                            }
                        }
                    }
                }

                // 4. Sinkronisasikan ke backend Laravel jika online
                if (this.isOnline && !this.currentOpenBillId) {
                    await this.syncOfflineQueue();
                }

                // Bebaskan meja jika dine in
                if (orderPayload.table_id) {
                    const tbl = this.tables.find(t => t.id === orderPayload.table_id);
                    if (tbl) tbl.is_occupied = false;
                }

                // 5. Tampilkan Modal Sukses Transaksi
                this.completedOrder = {
                    ...orderPayload,
                    changeDue: this.changeDue,
                    cashGiven: this.selectedPaymentMethod === 'CASH' ? this.cashGiven : this.totalMultiPaid
                };

                // Otomatis cetak struk via bluetooth jika printer siap
                if (window.thermalPrinter?.isConnected) {
                    window.thermalPrinter.printReceipt(this.completedOrder);
                }

                // Reset state transaksi
                this.cart = [];
                this.discount = 0;
                this.cashGiven = 0;
                this.multiCashAmount = 0;
                this.multiNonCashAmount = 0;
                this.currentOpenBillId = null;
                this.customerName = '';
                this.selectedTableId = '';
                this.selectedTableName = '';
                this.paymentModalOpen = false;
                this.mobileCartOpen = false;
                this.successModalOpen = true;

            } catch (err) {
                console.error('Gagal menyelesaikan pembayaran:', err);
                alert('Terjadi kesalahan saat memproses pembayaran: ' + err.message);
            } finally {
                this.isProcessingCheckout = false;
            }
        },

        // ----------------------------------------------------
        // KITCHEN ORDER TICKET (KOT)
        // ----------------------------------------------------

        printKitchenTicket(orderData) {
            const kotData = orderData || {
                order_number: this.completedOrder?.order_number || 'KOT-' + Math.floor(Math.random() * 9000 + 1000),
                table_name: this.selectedTableName || this.customerName || this.orderType,
                order_type: this.orderType,
                items: this.cart.length > 0 ? this.cart : (this.completedOrder?.items || []),
                created_at: new Date()
            };

            // Set data KOT sementara ke state window agar DOM KOT terupdate
            window.KOT_TEMP_DATA = kotData;
            if (window.thermalPrinter) {
                window.thermalPrinter.printKitchenTicket(kotData);
            } else {
                window.print();
            }
        },

        // ----------------------------------------------------
        // MANAJEMEN SHIFT & PETTY CASH
        // ----------------------------------------------------

        // Buka Shift Kasir Baru
        async startShift() {
            if (this.startCash < 0) {
                alert('Modal awal tidak boleh negatif!');
                return;
            }

            try {
                const outletId = window.POS_CONFIG?.outletId || '';
                const res = await fetch('/api/pos/shifts/start', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        outlet_id: outletId,
                        start_cash: this.startCash,
                        notes: this.startCashNotes
                    })
                });

                if (res.ok) {
                    const data = await res.json();
                    this.activeShift = data.shift;
                    this.startShiftModalOpen = false;
                    alert('Shift kasir berhasil dibuka dengan modal awal Rp' + formatThousand(this.startCash));
                } else {
                    const err = await res.json();
                    alert('Gagal buka shift: ' + (err.message || 'Kesalahan server'));
                }
            } catch (e) {
                alert('Gagal membuka shift kasir: ' + e.message);
            }
        },

        // Catat Petty Cash Out (Kas Keluar)
        async recordPettyCash() {
            if (this.pettyCashAmount <= 0) {
                alert('Masukkan nominal pengeluaran kas kecil!');
                return;
            }
            if (!this.pettyCashReason.trim()) {
                alert('Masukkan alasan / keterangan pengeluaran kas!');
                return;
            }
            if (!this.activeShift?.id) {
                alert('Shift kasir belum aktif!');
                return;
            }

            try {
                const res = await fetch('/api/pos/shifts/petty-cash', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        shift_id: this.activeShift.id,
                        amount: this.pettyCashAmount,
                        reason: this.pettyCashReason
                    })
                });

                if (res.ok) {
                    alert(`Kas keluar Rp${formatThousand(this.pettyCashAmount)} (${this.pettyCashReason}) berhasil dicatat.`);
                    this.pettyCashAmount = 0;
                    this.pettyCashReason = '';
                    this.pettyCashModalOpen = false;
                } else {
                    const err = await res.json();
                    alert('Gagal mencatat kas keluar: ' + (err.message || 'Kesalahan server'));
                }
            } catch (e) {
                alert('Gagal mencatat kas kecil: ' + e.message);
            }
        },

        // Tutup Shift Kasir (Blind Close)
        async closeShift() {
            if (this.actualCash < 0) {
                alert('Masukkan jumlah uang fisik aktual di laci!');
                return;
            }
            if (!this.activeShift?.id) {
                alert('Shift kasir belum aktif!');
                return;
            }

            try {
                const res = await fetch('/api/pos/shifts/close', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        shift_id: this.activeShift.id,
                        actual_cash: this.actualCash,
                        notes: this.closeShiftNotes
                    })
                });

                if (res.ok) {
                    const data = await res.json();
                    this.shiftReport = data.shift;
                    this.activeShift = null;
                    this.closeShiftModalOpen = false;
                    this.shiftSummaryModalOpen = true;
                } else {
                    const err = await res.json();
                    alert('Gagal menutup shift: ' + (err.message || 'Kesalahan server'));
                }
            } catch (e) {
                alert('Gagal menutup shift kasir: ' + e.message);
            }
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
                        if (res.synced_ids?.length > 0) {
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
