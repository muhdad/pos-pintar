<x-app-layout>
    @section('page_title', 'Restock Bahan Baku & Pembelian Pasar')

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{
        rawMaterials: {{ Js::from($rawMaterials) }},
        selectedMaterialId: '{{ $rawMaterials->first()?->id ?? '' }}',
        purchaseQty: 1,
        purchaseUnit: 'kg',
        priceInputType: 'total',
        pricePerUnit: 0,
        totalPrice: 0,
        supplierName: '',
        sourceAccount: 'CASH_DRAWER',
        transactionDate: '{{ date('Y-m-d') }}',
        notes: '',
        recordCashflow: true,
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
                // Jika satuan sistem adalah kg bukan gram
                if (this.systemUnit === 'kg') {
                    if (this.purchaseUnit === 'kg') return 1;
                    if (this.purchaseUnit === 'gram') return 0.001;
                    if (this.purchaseUnit === 'ons') return 0.1;
                }
                // Jika satuan sistem adalah liter bukan ml
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
            if (this.priceInputType === 'unit') {
                return (Number(this.purchaseQty) || 0) * (Number(this.pricePerUnit) || 0);
            }
            return Number(this.totalPrice) || 0;
        },

        get newStock() {
            return this.currentStock + this.convertedQty;
        },

        get newHpp() {
            const safeCurrent = Math.max(0, this.currentStock);
            const totalStock = safeCurrent + this.convertedQty;
            const cost = this.calculatedTotal;
            if (totalStock <= 0) return this.currentHpp;
            const valuation = (safeCurrent * this.currentHpp) + cost;
            return Math.round(valuation / totalStock);
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-base shadow-xs">
                        📦
                    </span>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Restock Bahan Baku & Pembelian Pasar</h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Konversi otomatis massa/volume (kg &rarr; gram, liter &rarr; ml) dan pemotongan langsung dari kas omset toko.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Cepat Pengeluaran Belanja Restock -->
            <div class="flex items-center gap-3">
                <div class="px-3.5 py-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Belanja Hari Ini</span>
                    <span class="text-sm font-bold text-slate-800 font-mono tabular-nums">
                        Rp {{ number_format($totalTodayRestock, 0, ',', '.') }}
                    </span>
                </div>
                <div class="px-3.5 py-2 bg-emerald-50/80 rounded-2xl border border-emerald-200/80 shadow-xs text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Total Restock Bulan Ini</span>
                    <span class="text-sm font-bold text-emerald-800 font-mono tabular-nums">
                        Rp {{ number_format($totalMonthRestock, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Grid: Form Restock Pintar (7 cols) + Riwayat Terkini (5 cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: Form Input Restock dengan Auto-Conversion -->
            <div class="lg:col-span-7 space-y-5">
                <form method="POST" action="{{ route('backoffice.restock.store') }}" class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                    @csrf

                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h2 class="text-sm font-bold text-slate-800">Form Pembelian & Restock Bahan</h2>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400">Auto-Convert & Moving Average HPP</span>
                    </div>

                    <!-- 1. Pilih Bahan Baku -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pilih Bahan Baku Resto <span class="text-rose-500">*</span>
                        </label>
                        <select name="raw_material_id" 
                                x-model="selectedMaterialId" 
                                @change="syncDefaultUnit()"
                                required
                                class="w-full text-xs rounded-xl border-slate-200 bg-white py-2.5 px-3 focus:ring-emerald-400 focus:border-emerald-400 font-medium text-slate-800 shadow-xs">
                            @foreach ($rawMaterials as $mat)
                                @php
                                    $stk = $mat->outletStocks->first()?->current_stock ?? 0;
                                @endphp
                                <option value="{{ $mat->id }}">
                                    {{ $mat->name }} (Satuan Sistem: {{ $mat->unit }} | Stok: {{ number_format($stk, 0, ',', '.') }} {{ $mat->unit }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Info Snapshot Bahan Terpilih -->
                        <template x-if="selectedMaterial">
                            <div class="mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between text-[11px]">
                                <span class="text-slate-600">
                                    Satuan di Sistem: <strong class="text-slate-800 uppercase font-mono" x-text="systemUnit"></strong>
                                </span>
                                <span class="text-slate-600">
                                    Stok Saat Ini: <strong class="text-slate-800 font-mono" x-text="currentStock.toLocaleString('id-ID') + ' ' + systemUnit"></strong>
                                </span>
                                <span class="text-slate-600">
                                    HPP Lama: <strong class="text-slate-800 font-mono" x-text="formatRupiah(currentHpp) + '/' + systemUnit"></strong>
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- 2. Kuantitas Beli & Satuan Beli (Auto-Convert Target) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kuantitas Beli di Pasar / Toko <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="purchase_quantity" 
                                   x-model.number="purchaseQty" 
                                   step="any" 
                                   min="0.0001" 
                                   required
                                   placeholder="Contoh: 1 atau 2.5"
                                   class="w-full text-xs rounded-xl border-slate-200 py-2.5 px-3 font-mono font-bold text-slate-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                            <span class="text-[10px] text-slate-400 mt-1 block">Contoh: Beli 1 (kg) atau 500 (gr)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Satuan Saat Beli <span class="text-rose-500">*</span>
                            </label>
                            <select name="purchase_unit" 
                                    x-model="purchaseUnit" 
                                    required
                                    class="w-full text-xs rounded-xl border-slate-200 bg-white py-2.5 px-3 font-medium text-slate-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                                <template x-for="u in availableUnits" :key="u.id">
                                    <option :value="u.id" x-text="u.label"></option>
                                </template>
                            </select>
                            <span class="text-[10px] text-slate-400 mt-1 block">Sistem otomatis mengalikan ke satuan dasar</span>
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

                    <!-- 3. Harga Beli (Total atau per Satuan) -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">
                                Nominal Pembelian Bahan <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2 text-[11px]">
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="radio" value="total" x-model="priceInputType" class="text-emerald-600 focus:ring-emerald-400">
                                    <span class="text-slate-600 font-medium">Total Harga Nota</span>
                                </label>
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="radio" value="unit" x-model="priceInputType" class="text-emerald-600 focus:ring-emerald-400">
                                    <span class="text-slate-600 font-medium">Harga / Satuan Beli</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="priceInputType === 'total'">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400 font-mono">Rp</span>
                                <input type="number" 
                                       name="total_price" 
                                       :value="calculatedTotal"
                                       @input="totalPrice = $event.target.value"
                                       min="0" 
                                       step="any"
                                       required
                                       placeholder="Contoh: 120000"
                                       class="w-full text-xs rounded-xl border-slate-200 pl-9 py-2.5 pr-3 font-mono font-bold text-slate-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Total uang keluar yang dibayar di pasar/supplier</span>
                        </div>

                        <div x-show="priceInputType === 'unit'" x-cloak>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400 font-mono">Rp</span>
                                <input type="number" 
                                       x-model.number="pricePerUnit" 
                                       min="0" 
                                       step="any"
                                       placeholder="Contoh: 120000 per kg"
                                       class="w-full text-xs rounded-xl border-slate-200 pl-9 py-2.5 pr-3 font-mono font-bold text-slate-800 focus:ring-emerald-400 focus:border-emerald-400 shadow-xs">
                            </div>
                            <div class="mt-1 flex justify-between items-center text-[11px] text-slate-500">
                                <span>Total Dihitung: <strong class="text-slate-800 font-mono font-bold" x-text="formatRupiah(calculatedTotal)"></strong></span>
                                <input type="hidden" name="total_price" :value="calculatedTotal">
                            </div>
                        </div>
                    </div>

                    <!-- 4. LIVE PREVIEW KONVERSI OTOMATIS (Hasil Kalkulasi) -->
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50/90 to-teal-50/80 border border-emerald-200/90 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-emerald-200/60">
                            <span class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                                <span>⚡</span> Hasil Konversi Otomatis ke Sistem
                            </span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-mono">Real-Time</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60">
                                <span class="text-[10px] text-slate-500 block">Beli di Pasar</span>
                                <span class="font-bold text-slate-800 font-mono text-sm block mt-0.5" x-text="(purchaseQty || 0) + ' ' + purchaseUnit"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60">
                                <span class="text-[10px] text-emerald-700 font-semibold block">Masuk ke Sistem</span>
                                <span class="font-bold text-emerald-800 font-mono text-sm block mt-0.5" x-text="'+' + convertedQty.toLocaleString('id-ID') + ' ' + systemUnit"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60">
                                <span class="text-[10px] text-slate-500 block">Stok Akhir Nanti</span>
                                <span class="font-bold text-slate-800 font-mono text-sm block mt-0.5" x-text="newStock.toLocaleString('id-ID') + ' ' + systemUnit"></span>
                            </div>
                            <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-200/60">
                                <span class="text-[10px] text-slate-500 block">HPP Baru / <span x-text="systemUnit"></span></span>
                                <span class="font-bold text-emerald-700 font-mono text-sm block mt-0.5" x-text="formatRupiah(newHpp)"></span>
                            </div>
                        </div>

                        <div class="text-[11px] text-emerald-800 flex items-center justify-between pt-1 font-medium">
                            <span>Total Pengeluaran Kas: <strong class="font-mono font-bold" x-text="formatRupiah(calculatedTotal)"></strong></span>
                            <span class="text-[10px] text-emerald-700 bg-white/70 px-2 py-0.5 rounded-lg border border-emerald-200">
                                HPP Lama: <span x-text="formatRupiah(currentHpp)"></span> &rarr; Baru: <span x-text="formatRupiah(newHpp)"></span>
                            </span>
                        </div>
                    </div>

                    <!-- 5. Opsi Tambahan (Supplier, Akun Kas, Tanggal, & Cashflow Toggle) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Toko / Pasar</label>
                            <input type="text" 
                                   name="supplier_name" 
                                   x-model="supplierName" 
                                   placeholder="Contoh: Pasar Pagi / Agen Beras"
                                   class="w-full text-xs rounded-xl border-slate-200 py-2 px-3 focus:ring-emerald-400 focus:border-emerald-400">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Sumber Dana Kas</label>
                            <select name="source_account" 
                                    x-model="sourceAccount" 
                                    class="w-full text-xs rounded-xl border-slate-200 py-2 px-3 bg-white focus:ring-emerald-400 focus:border-emerald-400">
                                <option value="CASH_DRAWER">Laci Kasir (Cash Drawer)</option>
                                <option value="BANK_ACCOUNT">Transfer Bank</option>
                                <option value="PETTY_CASH">Kas Kecil (Petty Cash)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Belanja</label>
                            <input type="date" 
                                   name="transaction_date" 
                                   x-model="transactionDate" 
                                   class="w-full text-xs rounded-xl border-slate-200 py-2 px-3 focus:ring-emerald-400 focus:border-emerald-400">
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Nota (Opsional)</label>
                        <input type="text" 
                               name="notes" 
                               x-model="notes" 
                               placeholder="Contoh: Kualitas super, dapat diskon dari langganan"
                               class="w-full text-xs rounded-xl border-slate-200 py-2 px-3 focus:ring-emerald-400 focus:border-emerald-400">
                    </div>

                    <!-- Checkbox Otomatis Potong Omset Kas -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" 
                                   name="record_cashflow" 
                                   value="1" 
                                   x-model="recordCashflow" 
                                   class="rounded-lg text-emerald-600 focus:ring-emerald-400 w-4 h-4">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Otomatis Catat Pengeluaran ke Arus Kas (Potong Omset Toko)</span>
                                <span class="text-[11px] text-slate-500">Mencatat pengeluaran di buku kas agar saldo omset & bagi hasil sinkron.</span>
                            </div>
                        </label>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800">Direkomendasikan</span>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Restock & Tambah Stok Gudang</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Riwayat Restock Terkini (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Riwayat Restock Terakhir</h3>
                            <span class="text-[11px] text-slate-500">Pencatatan mutasi stok bahan masuk</span>
                        </div>
                        <a href="{{ route('backoffice.inventory') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                            Gudang &rarr;
                        </a>
                    </div>

                    @if ($movements->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($movements as $m)
                                <div class="py-3 text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">
                                                +
                                            </span>
                                            <span class="font-bold text-slate-800">{{ $m->outletStock?->rawMaterial?->name ?? 'Bahan' }}</span>
                                        </div>
                                        <span class="font-bold text-emerald-700 font-mono tabular-nums">
                                            +{{ number_format($m->quantity_delta, 0, ',', '.') }} {{ $m->outletStock?->rawMaterial?->unit ?? 'gr' }}
                                        </span>
                                    </div>

                                    <p class="text-[11px] text-slate-500 line-clamp-2 pl-7">
                                        {{ $m->notes }}
                                    </p>

                                    <div class="flex items-center justify-between text-[10px] text-slate-400 pl-7 pt-0.5">
                                        <span>Sisa Stok: <strong class="text-slate-700 font-mono">{{ number_format($m->balance_after, 0, ',', '.') }} {{ $m->outletStock?->rawMaterial?->unit ?? 'gr' }}</strong></span>
                                        <span>{{ $m->created_at?->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-2">
                            {{ $movements->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400 text-xs">
                            <span class="text-3xl block mb-2">📦</span>
                            Belum ada riwayat restock bahan baku.
                        </div>
                    @endif
                </div>

                <!-- Petunjuk Konversi Satuan Singkat -->
                <div class="bg-indigo-50/60 rounded-3xl border border-indigo-100 p-4 text-xs space-y-2">
                    <div class="flex items-center gap-2 font-bold text-indigo-900">
                        <span>💡</span>
                        <span>Rumus & Konversi Otomatis Resto</span>
                    </div>
                    <ul class="text-[11px] text-indigo-800 space-y-1 pl-4 list-disc">
                        <li><strong>1 Kilogram (kg)</strong> = 1.000 gram. Jika bahan diatur gram, input 1 kg otomatis menambah +1.000 gr.</li>
                        <li><strong>1 Liter</strong> = 1.000 ml. Minyak goreng atau saus otomatis terkonversi ke mililiter.</li>
                        <li><strong>HPP Moving Average</strong> dihitung otomatis: nilai persediaan stok lama dijumlah dengan biaya pembelian baru, lalu dibagi total stok terbaru.</li>
                        <li><strong>Omset Bersih</strong> di dashboard dan pembagian hasil (split bill) otomatis mengurangi pengeluaran restock ini.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
