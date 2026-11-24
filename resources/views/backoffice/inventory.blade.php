<x-app-layout>
    @section('page_title', 'Stok Bahan Baku & Gudang')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-4 p-3 rounded-[6px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 rounded-[6px] bg-red-50 border border-red-200 text-red-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-[10px] font-bold">✕</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900 font-bold p-1">✕</button>
        </div>
    @endif

    <div x-data="{
        materialModalOpen: false,
        restockModalOpen: false,
        opnameModalOpen: false,
        importModalOpen: false,
        editMaterialModalOpen: false,
        ocrModalOpen: false,
        ocrScanning: false,
        ocrReceiptImage: null,
        ocrResult: null,
        previewImage: null,
        scanError: null,
        recordCashflow: true,
        rawMaterialsList: {{ Js::from($rawMaterials) }},

        openEditModal(mat, currentStock) {
            this.editingMaterial = {
                id: mat.id,
                name: mat.name,
                unit: mat.unit,
                average_cost: Number(mat.average_cost),
                minimum_stock: Number(mat.minimum_stock),
                current_stock: Number(currentStock)
            };
            this.editMaterialModalOpen = true;
        },

        onReceiptSelected(event) {
            const file = event.target.files[0];
            if (file) {
                this.ocrReceiptImage = file;
                this.previewImage = URL.createObjectURL(file);
                this.scanError = null;
                this.ocrResult = null;
            }
        },

        async submitReceiptScan() {
            if (!this.ocrReceiptImage) return;
            this.ocrScanning = true;
            this.scanError = null;
            const formData = new FormData();
            formData.append('receipt_image', this.ocrReceiptImage);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch('{{ route('backoffice.inventory.ocr-scan') }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const json = await res.json();
                if (json.success) {
                    this.ocrResult = json.data;
                } else {
                    this.scanError = json.message || 'Gagal memindai struk belanja.';
                }
            } catch (err) {
                this.scanError = 'Koneksi gagal saat menghubungi server OCR.';
            } finally {
                this.ocrScanning = false;
            }
        },

        removeOcrItem(index) {
            if (this.ocrResult && this.ocrResult.items) {
                this.ocrResult.items.splice(index, 1);
            }
        },

        recalcItemHpp(item) {
            const mat = this.rawMaterialsList.find(m => m.id === item.raw_material_id);
            if (mat) {
                const currentStock = mat.outlet_stocks && mat.outlet_stocks[0] ? Number(mat.outlet_stocks[0].current_stock) : 0;
                const currentHpp = Number(mat.average_cost);
                const addQty = Number(item.quantity) || 0;
                const addPrice = Number(item.total_price) || 0;
                if ((currentStock + addQty) > 0) {
                    item.new_hpp = Math.round(((currentStock * currentHpp) + addPrice) / (currentStock + addQty));
                } else {
                    item.new_hpp = addQty > 0 ? Math.round(addPrice / addQty) : currentHpp;
                }
            }
        },
        editingMaterial: {
            id: '',
            name: '',
            unit: 'gram',
            average_cost: 0,
            minimum_stock: 0,
            current_stock: 0
        }
    }" class="space-y-5">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200/80">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">Manajemen Bahan Baku & Gudang</h1>
                <p class="text-xs text-slate-500 mt-0.5">Monitoring kuantitas bahan baku riil, restock nota faktur, dan audit mutasi stok resep.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Export JSON Button -->
                <a href="{{ route('backoffice.catalog.export') }}" 
                   class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs"
                   title="Download file backup bahan baku & stok gudang (JSON)">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export</span>
                </a>

                <!-- Import JSON Button -->
                <button type="button" 
                        @click="importModalOpen = true"
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs"
                        title="Upload file data bahan baku & stok gudang (JSON)">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import</span>
                </button>

                <!-- Restock Bahan Cerdas -->
                <a href="{{ route('backoffice.restock') }}" 
                   class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Restock Bahan</span>
                </a>

                <button type="button" 
                        @click="opnameModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Stock Opname</span>
                </button>

                <button type="button" 
                        @click="materialModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Bahan Baru</span>
                </button>

                <!-- Export Excel Stok Gudang Button -->
                <a href="{{ route('backoffice.inventory.export-excel') }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs"
                   title="Export Seluruh Stok Bahan Baku & Valuasi ke File Excel">
                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>

        <!-- Raw Materials Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse ($rawMaterials as $material)
                @php
                    $currentStock = (float) ($material->outletStocks->first()->current_stock ?? 0);
                    $isLow = $currentStock <= (float) $material->minimum_stock;
                @endphp
                <div class="bauhaus-card p-4 flex flex-col justify-between relative overflow-hidden bg-white">
                    <div class="absolute top-0 left-0 w-1.5 h-full {{ $isLow ? 'bg-[#DC2626]' : 'bg-[#1D4ED8]' }}"></div>
                    <div class="pl-1">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 font-mono">{{ $material->unit }}</span>
                            <div class="flex items-center gap-1">
                                <span class="bauhaus-badge {{ $isLow ? 'bg-red-100 text-red-900 border-red-700' : 'bg-blue-50 text-blue-900 border-blue-700' }}">
                                    {{ $isLow ? 'MENIPIS' : 'AMAN' }}
                                </span>

                                <!-- Edit Material Button -->
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($material) }}, {{ $currentStock }})"
                                        class="p-1 rounded-[4px] text-stone-400 hover:text-stone-800 transition" 
                                        title="Edit Bahan Baku">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <!-- Delete Material Button -->
                                <form method="POST" 
                                      action="{{ route('backoffice.inventory.raw-material.destroy', $material->id) }}" 
                                      onsubmit="return confirm('Hapus bahan baku {{ $material->name }}?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-[4px] text-stone-400 hover:text-red-600 transition" title="Hapus Bahan Baku">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <h3 class="text-xs font-bold text-stone-900 leading-tight">{{ $material->name }}</h3>
                        <p class="text-[11px] text-stone-400 font-mono tabular-nums mt-0.5">HPP: Rp{{ number_format($material->average_cost, 0, ',', '.') }}/{{ $material->unit }}</p>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-end justify-between">
                        <div>
                            <span class="text-[10px] text-stone-400 block">Sisa Stok Riil</span>
                            <div class="text-lg font-bold {{ $isLow ? 'text-amber-800' : 'text-stone-900' }} font-mono tabular-nums">
                                {{ number_format($currentStock, 0, ',', '.') }} <span class="text-xs font-normal text-stone-500 font-sans">{{ $material->unit }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] text-stone-400 font-mono tabular-nums">Min: {{ (float) $material->minimum_stock }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-[10px] p-8 text-center border border-[#E7E0D8]">
                    <p class="text-xs font-semibold text-stone-600">Belum ada bahan baku di gudang.</p>
                    <p class="text-[11px] text-stone-400 mt-0.5">Klik tombol "+ Daftarkan Bahan Baku" di atas untuk menambahkan bahan baku pertama Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Stock Movements (Kartu Stok) Audit Table -->
        <div class="bg-white rounded-[10px] border border-[#E7E0D8] overflow-hidden">
            <div class="p-3.5 border-b border-[#E7E0D8] bg-[#FAF6F0]/40">
                <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Kartu Mutasi Stok Terbaru</h3>
                <p class="text-[11px] text-stone-500">Pencatatan otomatis pemotongan resep kasir, restock nota, dan opname.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-xs">
                    <thead>
                        <tr class="text-stone-500 font-bold border-b border-[#E7E0D8] bg-stone-50 uppercase text-[10px] tracking-wider sticky top-0 z-10">
                            <th class="py-2.5 px-4">Waktu</th>
                            <th class="py-2.5 px-3">Bahan Baku</th>
                            <th class="py-2.5 px-3">Tipe Mutasi</th>
                            <th class="py-2.5 px-3 text-right">Perubahan</th>
                            <th class="py-2.5 px-4">Keterangan / Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($movements as $mov)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-2.5 px-4 text-stone-500 font-mono tabular-nums text-[11px]">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-stone-900">
                                    {{ $mov->outletStock->rawMaterial->name ?? 'Bahan' }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-block px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold {{ $mov->type === 'IN' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($mov->type === 'OUT' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-stone-100 text-stone-700 border border-stone-200') }}">
                                        {{ $mov->type === 'IN' ? 'Masuk' : ($mov->type === 'OUT' ? 'Keluar' : 'Opname') }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold {{ $mov->type === 'IN' ? 'text-emerald-700' : ($mov->type === 'OUT' ? 'text-red-700' : 'text-stone-800') }} font-mono tabular-nums">
                                    {{ $mov->type === 'IN' ? '+' : ($mov->type === 'OUT' ? '-' : ($mov->quantity_delta >= 0 ? '+' : '')) }}{{ (float) $mov->quantity_delta }} {{ $mov->outletStock->rawMaterial->unit ?? '' }}
                                </td>
                                <td class="py-2.5 px-4 text-stone-600 text-[11px]">
                                    {{ $mov->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-stone-400">
                                    Belum ada catatan mutasi stok bahan baku.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftarkan Bahan Baku Modal -->
        <div x-show="materialModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="materialModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Daftarkan Bahan Baku</h3>
                    <button type="button" @click="materialModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.inventory.raw-material.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Bahan Baku <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Daging Ayam, Beras, Telur" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                            <select name="unit" required class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                <option value="gram">gram (gr)</option>
                                <option value="ml">mililiter (ml)</option>
                                <option value="pcs">pcs / butir</option>
                                <option value="kg">kilogram (kg)</option>
                                <option value="liter">liter (lt)</option>
                                <option value="lembar">lembar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">HPP per Satuan (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="average_cost" required min="0" placeholder="45" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Min. Stok <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="minimum_stock" required min="0" placeholder="1000" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Stok Awal</label>
                            <input type="number" step="any" name="initial_stock" min="0" placeholder="0" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="materialModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Simpan Bahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Bahan Baku Modal -->
        <div x-show="editMaterialModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="editMaterialModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Edit Data Bahan Baku</h3>
                    <button type="button" @click="editMaterialModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <form :action="'/backoffice/inventory/raw-material/' + editingMaterial.id" method="POST" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Bahan Baku <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="editingMaterial.name" required class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                            <select name="unit" x-model="editingMaterial.unit" required class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                <option value="gram">gram (gr)</option>
                                <option value="ml">mililiter (ml)</option>
                                <option value="pcs">pcs / butir</option>
                                <option value="kg">kilogram (kg)</option>
                                <option value="liter">liter (lt)</option>
                                <option value="lembar">lembar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">HPP per Satuan (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="average_cost" x-model="editingMaterial.average_cost" required min="0" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Min. Stok <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="minimum_stock" x-model="editingMaterial.minimum_stock" required min="0" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Koreksi Stok Riil</label>
                            <input type="number" step="any" name="current_stock" x-model="editingMaterial.current_stock" min="0" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="editMaterialModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Restock Gudang Modal -->
        <div x-show="restockModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="restockModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Restock Bahan Baku</h3>
                    <button type="button" @click="restockModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                @if($rawMaterials->isEmpty())
                    <div class="mt-3 p-3 rounded-[6px] bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        Belum ada bahan baku terdaftar. Silakan daftarkan bahan baku terlebih dahulu.
                    </div>
                @else
                    <form method="POST" action="{{ route('backoffice.inventory.restock') }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Pilih Bahan Baku <span class="text-red-500">*</span></label>
                            <select name="raw_material_id" required class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                @foreach($rawMaterials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->name }} (Satuan: {{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Jumlah Masuk <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="quantity" required min="0.01" placeholder="Contoh: 1000" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums font-bold focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan / Supplier / No. Nota</label>
                            <input type="text" name="notes" placeholder="Contoh: Pembelian supplier Pasar Baru" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="restockModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                            <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Tambah Stok</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Stock Opname Modal -->
        <div x-show="opnameModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="opnameModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Stock Opname Fisik Gudang</h3>
                        <p class="text-[10px] text-stone-500">Koreksi dan penyesuaian stok riil dengan pembukuan sistem.</p>
                    </div>
                    <button type="button" @click="opnameModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                @if($rawMaterials->isEmpty())
                    <div class="mt-3 p-3 rounded-[6px] bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        Belum ada bahan baku terdaftar untuk opname.
                    </div>
                @else
                    <form method="POST" action="{{ route('backoffice.inventory.opname') }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Pilih Bahan Baku <span class="text-red-500">*</span></label>
                            <select name="raw_material_id" required class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                @foreach($rawMaterials as $mat)
                                    @php
                                        $stockNow = (float) ($mat->outletStocks->first()->current_stock ?? 0);
                                    @endphp
                                    <option value="{{ $mat->id }}">{{ $mat->name }} (Sistem: {{ number_format($stockNow, 0, ',', '.') }} {{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Hasil Hitung Fisik Aktual <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="physical_stock" required min="0" placeholder="Contoh: 4500" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tabular-nums font-bold focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                            <span class="text-[10px] text-stone-400 mt-0.5 block">Sistem akan menghitung selisih dan mencatat kartu mutasi stok & audit log.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Alasan Penyesuaian / Keterangan <span class="text-red-500">*</span></label>
                            <input type="text" name="notes" required placeholder="Contoh: Selisih tumpah saat racik / Opname akhir pekan" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="opnameModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                            <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#5B0A14] hover:bg-[#7A0F1C] text-white text-xs font-bold transition">Simpan Opname</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Import Bahan & Stok JSON Modal -->
        <div x-show="importModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="importModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Import Bahan Baku & Stok</h3>
                        <p class="text-[10px] text-stone-500">Upload file JSON hasil export dari komputer lokal.</p>
                    </div>
                    <button type="button" @click="importModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.catalog.import') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Pilih File JSON <span class="text-red-500">*</span></label>
                        <input type="file" 
                               name="catalog_file" 
                               accept=".json" 
                               required 
                               class="w-full text-xs text-stone-700 file:mr-2 file:py-1.5 file:px-3 file:rounded-[6px] file:border-0 file:text-xs file:font-semibold file:bg-[#5B0A14] file:text-white hover:file:bg-[#7A0F1C] cursor-pointer">
                        <span class="text-[10px] text-stone-400 mt-1 block">File yang didukung: <code>.json</code> hasil export dari Backoffice.</span>
                    </div>

                    <div class="p-2.5 rounded-[6px] bg-amber-50 border border-amber-200 text-amber-900 text-[11px] space-y-1">
                        <span class="font-bold block">Yang Otomatis Disinkronkan:</span>
                        <ul class="list-disc list-inside space-y-0.5 text-stone-600">
                            <li>Daftar Bahan Baku & HPP Satuan</li>
                            <li><strong>Saldo Sisa Stok Riil Gudang</strong></li>
                            <li>Katalog Menu & Takaran Resep (BOM)</li>
                        </ul>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="importModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Auto Restock via Struk AI Modal (Bauhaus Style) -->
        <div x-show="ocrModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bauhaus-card max-w-2xl w-full p-5 bg-white shadow-modal animate-in fade-in max-h-[90vh] overflow-y-auto"
                 @click.outside="ocrModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b-2 border-stone-900 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#FACC15] border border-black"></span>
                        <div>
                            <h3 class="text-sm font-display font-black text-[#121212] uppercase tracking-wide">
                                📸 Auto Restock via Foto Struk (AI OCR)
                            </h3>
                            <p class="text-[10px] text-stone-500 font-mono">Pindai nota pasar/supplier, sesuaikan stok riil, dan hitung HPP Moving Average secara otomatis.</p>
                        </div>
                    </div>
                    <button type="button" @click="ocrModalOpen = false" class="text-stone-900 hover:text-red-600 font-black p-1">✕</button>
                </div>

                <!-- Step 1: Upload / Capture Struk -->
                <div class="space-y-4">
                    <div class="p-3 bg-[#F6F4ED] border-2 border-dashed border-stone-900 text-center">
                        <input type="file" 
                               id="receipt_file_input"
                               accept="image/*" 
                               capture="environment" 
                               @change="onReceiptSelected($event)" 
                               class="hidden">
                        
                        <div x-show="!previewImage" class="py-4 cursor-pointer" @click="document.getElementById('receipt_file_input').click()">
                            <svg class="w-8 h-8 mx-auto text-stone-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span class="font-bold text-xs text-stone-800 block">Klik untuk Ambil Foto Kamera / Pilih File Struk</span>
                            <span class="text-[10px] text-stone-400 font-mono mt-1 block">Format: JPG, PNG, WEBP (Maksimal 10MB)</span>
                        </div>

                        <div x-show="previewImage" class="flex flex-col sm:flex-row items-center gap-4 py-2">
                            <img :src="previewImage" alt="Preview Struk" class="h-28 w-auto border-2 border-stone-900 object-contain bg-white">
                            <div class="text-left flex-1">
                                <span class="font-bold text-xs text-stone-900 block" x-text="ocrReceiptImage ? ocrReceiptImage.name : ''"></span>
                                <span class="text-[10px] text-stone-500 font-mono block mt-0.5">Siap dianalisis oleh AI OCR.</span>
                                <div class="mt-2.5 flex items-center gap-2">
                                    <button type="button" 
                                            @click="document.getElementById('receipt_file_input').click()" 
                                            class="bauhaus-btn-white text-[11px] py-1 px-2.5">
                                        Ganti Foto
                                    </button>
                                    <button type="button" 
                                            @click="submitReceiptScan()" 
                                            :disabled="ocrScanning" 
                                            class="bauhaus-btn-blue text-[11px] py-1 px-3">
                                        <span x-show="!ocrScanning">🤖 Mulai Pindai AI</span>
                                        <span x-show="ocrScanning">Memproses OCR...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div x-show="scanError" class="p-2.5 bg-red-100 border-2 border-red-700 text-red-900 text-xs font-bold font-mono">
                        <span x-text="scanError"></span>
                    </div>

                    <!-- Spinner During Scan -->
                    <div x-show="ocrScanning" class="p-6 bg-white border-2 border-stone-900 text-center space-y-2">
                        <div class="inline-block animate-spin rounded-none h-6 w-6 border-4 border-stone-900 border-t-[#FACC15]"></div>
                        <p class="text-xs font-bold text-stone-800">Sedang mengekstrak teks nota belanja & mencocokkan bahan baku...</p>
                        <span class="text-[10px] text-stone-500 font-mono">Mengkalkulasi Moving Average Cost (HPP baru) secara otomatis.</span>
                    </div>

                    <!-- Step 2: Extracted Items Table (Form Confirm) -->
                    <div x-show="ocrResult" class="space-y-4 pt-2">
                        <form method="POST" action="{{ route('backoffice.inventory.ocr-confirm') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="receipt_image_path" :value="ocrResult ? ocrResult.receipt_image_path : ''">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-stone-50 border-2 border-stone-900">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-stone-600 font-mono mb-1">Nama Toko / Supplier</label>
                                    <input type="text" 
                                           name="merchant_name" 
                                           x-model="ocrResult ? ocrResult.merchant_name : ''" 
                                           required 
                                           class="bauhaus-input w-full text-xs">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-stone-600 font-mono mb-1">Tanggal Struk</label>
                                    <input type="date" 
                                           name="receipt_date" 
                                           x-model="ocrResult ? ocrResult.receipt_date : ''" 
                                           class="bauhaus-input w-full text-xs font-mono">
                                </div>
                            </div>

                            <!-- Extracted Items -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-black uppercase tracking-wider text-[#121212] font-mono">ITEM BAHAN TERDETEKSI</span>
                                    <span class="text-[10px] text-stone-500 font-mono">Pastikan bahan & harga sesuai nota</span>
                                </div>

                                <div class="border-2 border-stone-900 overflow-x-auto">
                                    <table class="w-full text-left text-xs min-w-[550px]">
                                        <thead>
                                            <tr class="bg-[#121212] text-white font-mono font-black text-[9px] uppercase tracking-wider">
                                                <th class="py-2 px-2.5">Item Nota</th>
                                                <th class="py-2 px-2.5">Cocokkan Bahan Baku</th>
                                                <th class="py-2 px-2">Qty Restock</th>
                                                <th class="py-2 px-2.5">Total Bayar (Rp)</th>
                                                <th class="py-2 px-2">HPP Baru</th>
                                                <th class="py-2 px-1 text-center">✕</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y-2 divide-stone-200 bg-white">
                                            <template x-for="(item, index) in (ocrResult ? ocrResult.items : [])" :key="index">
                                                <tr class="hover:bg-amber-50/50">
                                                    <!-- Raw Name in Struk -->
                                                    <td class="py-2 px-2.5 font-bold text-stone-800 text-[11px]" x-text="item.raw_name"></td>

                                                    <!-- Material Select Dropdown -->
                                                    <td class="py-2 px-2.5">
                                                        <select :name="'items[' + index + '][raw_material_id]'" 
                                                                x-model="item.matched_material_id" 
                                                                @change="recalcItemHpp(item)"
                                                                required 
                                                                class="bauhaus-input text-[11px] py-1 px-1.5 w-full">
                                                            <option value="">-- Pilih Bahan Baku --</option>
                                                            <template x-for="mat in rawMaterialsList" :key="mat.id">
                                                                <option :value="mat.id" 
                                                                        :selected="mat.id === item.matched_material_id" 
                                                                        x-text="mat.name + ' (' + mat.unit + ')'"></option>
                                                            </template>
                                                        </select>
                                                    </td>

                                                    <!-- Quantity -->
                                                    <td class="py-2 px-2 whitespace-nowrap">
                                                        <div class="flex items-center gap-1">
                                                            <input type="number" 
                                                                   :name="'items[' + index + '][quantity]'" 
                                                                   x-model="item.quantity" 
                                                                   @input="recalcItemHpp(item)"
                                                                   min="0.01" 
                                                                   step="any" 
                                                                   required 
                                                                   class="bauhaus-input text-xs font-mono py-1 px-1.5 w-20">
                                                            <span class="text-[10px] font-mono text-stone-500" x-text="item.unit"></span>
                                                        </div>
                                                    </td>

                                                    <!-- Total Line Price -->
                                                    <td class="py-2 px-2.5">
                                                        <input type="number" 
                                                               :name="'items[' + index + '][total_price]'" 
                                                               x-model="item.total_price" 
                                                               @input="recalcItemHpp(item)"
                                                               min="0" 
                                                               step="1" 
                                                               required 
                                                               class="bauhaus-input text-xs font-mono py-1 px-1.5 w-24">
                                                    </td>

                                                    <!-- New HPP (Weighted Moving Average) -->
                                                    <td class="py-2 px-2 font-mono whitespace-nowrap">
                                                        <span class="bauhaus-badge bg-emerald-50 text-emerald-900 border-emerald-700 block text-[9px]" 
                                                              x-text="'Rp ' + Number(item.new_hpp || 0).toLocaleString('id-ID') + '/' + item.unit"></span>
                                                    </td>

                                                    <!-- Remove Line -->
                                                    <td class="py-2 px-1 text-center">
                                                        <button type="button" @click="removeOcrItem(index)" class="text-red-500 hover:text-red-800 font-bold p-1">✕</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Checkbox: Potong dari Omzet H-1 & Catat ke Arus Kas -->
                            <div class="p-3 bg-amber-50 border-2 border-stone-900 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="record_cashflow" 
                                           id="record_cashflow_check" 
                                           value="1" 
                                           checked 
                                           class="border-2 border-black text-[#1D4ED8] focus:ring-0">
                                    <label for="record_cashflow_check" class="font-bold text-xs text-stone-900 cursor-pointer">
                                        Catat ke Arus Kas & Potong dari Omzet H-1
                                    </label>
                                </div>
                                <span class="bauhaus-badge bg-[#FACC15] text-[#121212]">PERPUTARAN H-1</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 flex justify-end gap-2 border-t-2 border-stone-200">
                                <button type="button" @click="ocrModalOpen = false" class="bauhaus-btn-white">Batal</button>
                                <button type="submit" class="bauhaus-btn-yellow font-black">
                                    ✓ Konfirmasi & Restock Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
