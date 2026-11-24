<x-app-layout>
    @section('page_title', 'Restock Bahan Baku')

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{
        rawMaterials: {{ Js::from($rawMaterials) }},
        selectedMaterialId: '{{ $rawMaterials->first()?->id ?? '' }}',
        purchaseQty: 1,
        purchaseUnit: 'kg',
        showExpenseSection: false,
        priceInputType: 'total',
        pricePerUnit: '',
        totalPrice: '',
        supplierName: '',
        sourceAccount: 'CASH_DRAWER',
        transactionDate: '{{ date('Y-m-d') }}',
        notes: '',
        recordCashflow: false,
        customFactor: null,

        get selectedMaterial() {
            return this.rawMaterials.find(m => m.id === this.selectedMaterialId) || null;
        },

        get systemUnit() {
            return this.selectedMaterial ? (this.selectedMaterial.unit || 'gram').toLowerCase() : 'gram';
        },

        get currentStock() {
            if (!this.selectedMaterial || !this.selectedMaterial.outlet_stocks || !this.selectedMaterial.outlet_stocks[0]) {
                return 0;
            }
            return Number(this.selectedMaterial.outlet_stocks[0].current_stock) || 0;
        },

        get currentHpp() {
            return this.selectedMaterial ? Number(this.selectedMaterial.average_cost) || 0 : 0;
        },

        get unitCategory() {
            const u = this.systemUnit;
            if (['gram', 'gr', 'g', 'kg', 'ons'].includes(u)) return 'mass';
            if (['ml', 'liter', 'l'].includes(u)) return 'volume';
            return 'count';
        },

        get availableUnits() {
            const cat = this.unitCategory;
            if (cat === 'mass') {
                return [
                    { id: 'kg', label: 'Kilogram (kg) — x1.000 gram', factor: 1000 },
                    { id: 'ons', label: 'Ons (100 gram) — x100 gram', factor: 100 },
                    { id: 'gram', label: 'Gram (gr) — 1:1', factor: 1 },
                    { id: 'kuintal', label: 'Kuintal (100 kg) — x100.000 gr', factor: 100000 },
                    { id: 'custom', label: 'Kemasan Kustom (Isi Sendiri)', factor: 0 }
                ];
            } else if (cat === 'volume') {
                return [
                    { id: 'liter', label: 'Liter (L) — x1.000 ml', factor: 1000 },
                    { id: 'ml', label: 'Mililiter (ml) — 1:1', factor: 1 },
                    { id: 'galon', label: 'Galon (19 Liter) — x19.000 ml', factor: 19000 },
                    { id: 'custom', label: 'Kemasan Kustom (Isi Sendiri)', factor: 0 }
                ];
            } else {
                return [
                    { id: 'pcs', label: 'Pcs / Butir / Biji — 1:1', factor: 1 },
                    { id: 'lusin', label: 'Lusin (12 pcs) — x12', factor: 12 },
                    { id: 'kodi', label: 'Kodi (20 pcs) — x20', factor: 20 },
                    { id: 'tray', label: 'Tray / Rak Telur (30 butir) — x30', factor: 30 },
                    { id: 'custom', label: 'Kemasan Kustom (Isi Sendiri)', factor: 0 }
                ];
            }
        },

        init() {
            this.syncDefaultUnit();
        },

        syncDefaultUnit() {
            const cat = this.unitCategory;
            if (cat === 'mass') {
                this.purchaseUnit = this.systemUnit === 'kg' ? 'kg' : 'kg';
            } else if (cat === 'volume') {
                this.purchaseUnit = this.systemUnit === 'liter' ? 'liter' : 'liter';
            } else {
                this.purchaseUnit = 'pcs';
            }
        },

        get conversionFactor() {
            if (this.purchaseUnit === 'custom') {
                return Number(this.customFactor) || 1;
            }
            const found = this.availableUnits.find(u => u.id === this.purchaseUnit);
            if (found) {
                if (this.systemUnit === 'kg') {
                    if (this.purchaseUnit === 'kg') return 1;
                    if (this.purchaseUnit === 'gram') return 0.001;
                    if (this.purchaseUnit === 'ons') return 0.1;
                }
                if (this.systemUnit === 'liter') {
                    if (this.purchaseUnit === 'liter') return 1;
                    if (this.purchaseUnit === 'ml') return 0.001;
                    if (this.purchaseUnit === 'galon') return 19;
                }
                return found.factor;
            }
            return 1;
        },

        get convertedQty() {
            const q = Number(this.purchaseQty) || 0;
            return Math.round(q * this.conversionFactor * 1000) / 1000;
        },

        get calculatedTotal() {
            if (!this.showExpenseSection) return 0;
            if (this.priceInputType === 'unit') {
                return (Number(this.purchaseQty) || 0) * (Number(this.pricePerUnit) || 0);
            }
            return Number(this.totalPrice) || 0;
        },

        get newStock() {
            return this.currentStock + this.convertedQty;
        },

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        }
    }">

        <!-- Flash Status Notification -->
        @if (session('status'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-medium space-y-1 shadow-xs">
                <div class="flex items-center gap-2 font-bold">
                    <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs">✕</span>
                    <span>Periksa kesalahan input berikut:</span>
                </div>
                <ul class="list-disc list-inside pl-7 text-[11px] text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-stone-200/80">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-base shadow-xs">
                        📦
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-stone-800 tracking-tight">Restock Stok Bahan Baku</h1>
                        <p class="text-xs text-stone-500 mt-0.5">
                            Penambahan stok fisik bahan dengan konversi satuan otomatis (kg &rarr; gram, liter &rarr; ml). HPP menu & bahan murni manual.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="flex items-center gap-2">
                <a href="{{ route('backoffice.inventory') }}" 
                   class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <span>📋</span>
                    <span>Gudang Bahan Baku</span>
                </a>
                <a href="{{ route('backoffice.products') }}" 
                   class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <span>🍲</span>
                    <span>Kelola Menu & HPP</span>
                </a>
            </div>
        </div>

        <!-- Banner Info Penting HPP Manual -->
        <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex items-start sm:items-center gap-3 text-xs text-amber-900 shadow-xs">
            <span class="text-base leading-none">💡</span>
            <div class="flex-1">
                <span class="font-bold">Restock Murni Fisik Bahan & HPP Manual:</span>
                <span class="text-amber-800 text-[11px] block sm:inline sm:ml-1">
                    Cukup masukkan jumlah bahan yang dibeli di pasar. Sistem hanya menambah stok fisik di sistem. Nilai HPP menu & modal bahan baku tidak akan diubah atau ditimpa otomatis.
                </span>
            </div>
        </div>

        <!-- Main Grid: Form Restock Pintar (7 cols) + Riwayat Terkini (5 cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: Form Input Restock Murni Bahan -->
            <div class="lg:col-span-7 space-y-5">
                <form method="POST" action="{{ route('backoffice.restock.store') }}" class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200/80 p-4 sm:p-6 shadow-xs space-y-5">
                    @csrf

                    <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h2 class="text-sm font-bold text-stone-800">Form Tambah Stok Bahan</h2>
                        </div>
                        <span class="text-[11px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                            Konversi Otomatis Aktif
                        </span>
                    </div>

                    <!-- 1. Pilih Bahan Baku -->
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">
                            Pilih Bahan Baku <span class="text-rose-500">*</span>
                        </label>
                        <select name="raw_material_id" 
                                x-model="selectedMaterialId" 
                                @change="syncDefaultUnit()"
                                required
                                class="w-full text-xs rounded-xl border-stone-200 bg-white py-2.5 px-3 focus:ring-emerald-400 focus:border-emerald-400 font-medium text-stone-800 shadow-xs">
                            @foreach ($rawMaterials as $mat)
                                @php
                                    $stk = $mat->outletStocks->first()?->current_stock ?? 0;
                                @endphp
                                <option value="{{ $mat->id }}">
                                    {{ $mat->name }} (Satuan: {{ $mat->unit }} | Stok: {{ number_format($stk, 0, ',', '.') }} {{ $mat->unit }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Info Snapshot Bahan Terpilih -->
                        <template x-if="selectedMaterial">
                            <div class="mt-2 p-2.5 rounded-xl bg-stone-50 border border-stone-200/70 flex items-center justify-between text-[11px]">
                                <span class="text-stone-600">
                                    Satuan Dasar: <strong class="text-stone-800 uppercase font-mono" x-text="systemUnit"></strong>
                                </span>
                                <span class="text-stone-600">
                                    Stok Saat Ini: <strong class="text-emerald-800 font-mono font-bold" x-text="currentStock.toLocaleString('id-ID') + ' ' + systemUnit"></strong>
                                </span>
                                <span class="text-stone-600">
                                    HPP Manual: <strong class="text-stone-700 font-mono" x-text="formatRupiah(currentHpp) + '/' + systemUnit"></strong>
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- 2. Kuantitas Beli & Satuan Beli (Target Auto-Conversion) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">
                                Kuantitas Beli di Pasar / Toko <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="purchase_quantity" 
                                   x-model.number="purchaseQty" 
                                   step="any" 
                                   min="0.0001" 
                                   required
                                   placeholder="Contoh: 1 atau 2.5"
                                   class="w-full text-xs rounded-xl border-stone-200 py-2.5 px-3 font-mono font-bold text-stone-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                            <span class="text-[10px] text-stone-400 mt-1 block">Contoh: Beli 1 (kg) atau 500 (gr)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">
                                Satuan Saat Beli <span class="text-rose-500">*</span>
                            </label>
                            <select name="purchase_unit" 
                                    x-model="purchaseUnit" 
                                    required
                                    class="w-full text-xs rounded-xl border-stone-200 bg-white py-2.5 px-3 font-medium text-stone-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                                <template x-for="u in availableUnits" :key="u.id">
                                    <option :value="u.id" x-text="u.label"></option>
                                </template>
                            </select>
                            <span class="text-[10px] text-stone-400 mt-1 block">Sistem otomatis mengonversi ke satuan dasar</span>
                        </div>
                    </div>

                    <!-- Input Kustom Faktor (Hanya muncul jika pilih custom) -->
                    <div x-show="purchaseUnit === 'custom'" x-cloak class="p-3 bg-amber-50 rounded-2xl border border-amber-200 text-xs space-y-1.5">
                        <label class="block font-bold text-amber-900">
                            1 Kemasan Kustom Berisi Berapa <span class="uppercase font-mono" x-text="systemUnit"></span>?
                        </label>
                        <input type="number" 
                               name="custom_conversion_factor" 
                               x-model.number="customFactor" 
                               step="any" 
                               placeholder="Contoh: 1 dus = 24 botol / 1 karung = 25000 gr"
                               class="w-full text-xs rounded-xl border-amber-300 py-2 px-3 font-mono">
                    </div>

                    <!-- 3. LIVE PREVIEW KONVERSI OTOMATIS (Hasil Kalkulasi Fisik Stok) -->
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50/90 to-teal-50/80 border border-emerald-200/90 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-emerald-200/60">
                            <span class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                                <span>⚡</span> Hasil Konversi Otomatis ke Sistem
                            </span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono">Instan</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 sm:gap-3 text-xs">
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60 min-w-0">
                                <span class="text-[10px] text-stone-500 block truncate">Beli di Pasar</span>
                                <span class="font-bold text-stone-800 font-mono text-xs sm:text-sm block mt-0.5 truncate" x-text="(purchaseQty || 0) + ' ' + purchaseUnit"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60 min-w-0">
                                <span class="text-[10px] text-emerald-700 font-semibold block truncate">Masuk ke Sistem</span>
                                <span class="font-bold text-emerald-800 font-mono text-xs sm:text-sm block mt-0.5 truncate" x-text="'+' + convertedQty.toLocaleString('id-ID') + ' ' + systemUnit"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60 min-w-0">
                                <span class="text-[10px] text-stone-500 block truncate">Stok Akhir Nanti</span>
                                <span class="font-bold text-stone-800 font-mono text-xs sm:text-sm block mt-0.5 truncate" x-text="newStock.toLocaleString('id-ID') + ' ' + systemUnit"></span>
                            </div>
                        </div>

                        <div class="text-[11px] text-emerald-800 flex items-center justify-between pt-1 font-medium">
                            <span>Status HPP Bahan: <strong class="font-mono font-bold">Manual (Tetap Rp <span x-text="currentHpp.toLocaleString('id-ID')"></span>/<span x-text="systemUnit"></span>)</strong></span>
                            <span class="text-[10px] text-emerald-700 bg-white/70 px-2 py-0.5 rounded-lg border border-emerald-200">
                                Stok bertambah instan
                            </span>
                        </div>
                    </div>

                    <!-- 4. Section Biaya & Arus Kas (Opsional) -->
                    <div class="pt-2 border-t border-stone-100">
                        <button type="button" 
                                @click="showExpenseSection = !showExpenseSection" 
                                class="w-full flex items-center justify-between p-3 rounded-2xl bg-stone-50 hover:bg-stone-100/80 border border-stone-200/70 text-xs font-semibold text-stone-700 transition">
                            <span class="flex items-center gap-2">
                                <span class="text-sm">💰</span>
                                <span>Catat Nominal Harga Beli & Potong Kas Toko (Opsional)</span>
                            </span>
                            <span class="text-xs text-stone-400 font-mono" x-text="showExpenseSection ? '▲ Tutup' : '▼ Buka'"></span>
                        </button>

                        <div x-show="showExpenseSection" x-cloak class="mt-3 p-4 bg-stone-50/70 rounded-2xl border border-stone-200/80 space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-stone-700">
                                        Nominal Pembelian Bahan (Opsional)
                                    </label>
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <label class="inline-flex items-center gap-1 cursor-pointer">
                                            <input type="radio" value="total" x-model="priceInputType" class="text-emerald-600 focus:ring-emerald-400">
                                            <span class="text-stone-600 font-medium">Total Nota</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1 cursor-pointer">
                                            <input type="radio" value="unit" x-model="priceInputType" class="text-emerald-600 focus:ring-emerald-400">
                                            <span class="text-stone-600 font-medium">Harga/Satuan</span>
                                        </label>
                                    </div>
                                </div>

                                <div x-show="priceInputType === 'total'">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-stone-400 font-mono">Rp</span>
                                        <input type="number" 
                                               name="total_price" 
                                               x-model="totalPrice"
                                               min="0" 
                                               step="any"
                                               placeholder="Kosongkan jika hanya ingin restock bahan"
                                               class="w-full text-xs rounded-xl border-stone-200 pl-9 py-2.5 pr-3 font-mono font-bold text-stone-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                                    </div>
                                </div>

                                <div x-show="priceInputType === 'unit'" x-cloak>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-stone-400 font-mono">Rp</span>
                                        <input type="number" 
                                               x-model.number="pricePerUnit" 
                                               min="0" 
                                               step="any"
                                               placeholder="Contoh: 120000 per kg"
                                               class="w-full text-xs rounded-xl border-stone-200 pl-9 py-2.5 pr-3 font-mono font-bold text-stone-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                                    </div>
                                    <div class="mt-1 flex justify-between items-center text-[11px] text-stone-500">
                                        <span>Total: <strong class="text-stone-800 font-mono font-bold" x-text="formatRupiah(calculatedTotal)"></strong></span>
                                        <input type="hidden" name="total_price" :value="calculatedTotal">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <label class="block font-semibold text-stone-700 mb-1">Sumber Dana Kas</label>
                                    <select name="source_account" 
                                            x-model="sourceAccount" 
                                            class="w-full text-xs rounded-xl border-stone-200 py-2 px-3 bg-white focus:ring-emerald-400 focus:border-emerald-400">
                                        <option value="CASH_DRAWER">Laci Kasir (Cash Drawer)</option>
                                        <option value="BANK_ACCOUNT">Transfer Bank</option>
                                        <option value="PETTY_CASH">Kas Kecil (Petty Cash)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-stone-700 mb-1">Tanggal Belanja</label>
                                    <input type="date" 
                                           name="transaction_date" 
                                           x-model="transactionDate" 
                                           class="w-full text-xs rounded-xl border-stone-200 py-2 px-3 focus:ring-emerald-400 focus:border-emerald-400">
                                </div>
                            </div>

                            <div class="p-2.5 bg-white rounded-xl border border-stone-200 flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="record_cashflow" 
                                           value="1" 
                                           x-model="recordCashflow" 
                                           class="rounded text-emerald-600 focus:ring-emerald-400 w-4 h-4">
                                    <span class="text-xs text-stone-700 font-medium">Catat pengeluaran ke buku kas (potong kas omset toko)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Info Pelengkap (Toko / Catatan) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-stone-700 mb-1">Nama Toko / Pasar (Opsional)</label>
                            <input type="text" 
                                   name="supplier_name" 
                                   x-model="supplierName" 
                                   placeholder="Contoh: Pasar Pagi / Agen Daging"
                                   class="w-full text-xs rounded-xl border-stone-200 py-2 px-3 focus:ring-emerald-400 focus:border-emerald-400">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" 
                                   name="notes" 
                                   x-model="notes" 
                                   placeholder="Contoh: Daging sapi bagian paha"
                                   class="w-full text-xs rounded-xl border-stone-200 py-2 px-3 focus:ring-emerald-400 focus:border-emerald-400">
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Tambah Stok Bahan Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Riwayat Restock Terkini (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-3xl border border-stone-200/80 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                        <div>
                            <h3 class="text-xs font-bold text-stone-800 uppercase tracking-wide">Riwayat Mutasi Bahan Masuk</h3>
                            <span class="text-[11px] text-stone-500">Pencatatan penambahan stok bahan baku</span>
                        </div>
                        <a href="{{ route('backoffice.inventory') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                            Gudang &rarr;
                        </a>
                    </div>

                    @if ($movements->isNotEmpty())
                        <div class="divide-y divide-stone-100">
                            @foreach ($movements as $m)
                                <div class="py-3 text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">
                                                +
                                            </span>
                                            <span class="font-bold text-stone-800">{{ $m->outletStock?->rawMaterial?->name ?? 'Bahan' }}</span>
                                        </div>
                                        <span class="font-bold text-emerald-700 font-mono tabular-nums">
                                            +{{ number_format($m->quantity_delta, 0, ',', '.') }} {{ $m->outletStock?->rawMaterial?->unit ?? 'gr' }}
                                        </span>
                                    </div>

                                    <p class="text-[11px] text-stone-500 line-clamp-2 pl-7">
                                        {{ $m->notes }}
                                    </p>

                                    <div class="flex items-center justify-between text-[10px] text-stone-400 pl-7 pt-0.5">
                                        <span>Sisa Stok: <strong class="text-stone-700 font-mono">{{ number_format($m->balance_after, 0, ',', '.') }} {{ $m->outletStock?->rawMaterial?->unit ?? 'gr' }}</strong></span>
                                        <span>{{ $m->created_at?->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-2">
                            {{ $movements->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 text-stone-400 text-xs">
                            <span class="text-3xl block mb-2">📦</span>
                            Belum ada riwayat restock bahan baku.
                        </div>
                    @endif
                </div>

                <!-- Petunjuk Ringkas Sistem & HPP Manual -->
                <div class="bg-stone-50/80 rounded-3xl border border-stone-200/80 p-4 text-xs space-y-2">
                    <div class="flex items-center gap-2 font-bold text-stone-800">
                        <span>💡</span>
                        <span>Prinsip Stok & HPP di Toko Anda</span>
                    </div>
                    <ul class="text-[11px] text-stone-600 space-y-1.5 pl-4 list-disc">
                        <li><strong>Restock Satuan Pintar:</strong> Masukkan 1 kg, otomatis tercatat +1.000 gram ke dalam stok bahan dasar dapur Anda.</li>
                        <li><strong>HPP Murni Manual:</strong> Anda memegang kendali penuh atas nilai modal menu & bahan di menu Produk & Gudang Bahan Baku.</li>
                        <li><strong>Kasir POS:</strong> Setiap transaksi pesanan berhasil, sistem memotong stok bahan baku resep secara otomatis tanpa merubah harga modal manual Anda.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
