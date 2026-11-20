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
        editMaterialModalOpen: false,
        editingMaterial: {
            id: '',
            name: '',
            unit: 'gram',
            average_cost: 0,
            minimum_stock: 0,
            current_stock: 0
        },

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
        }
    }" class="space-y-5">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#E7E0D8]">
            <div>
                <h1 class="text-lg sm:text-xl font-display font-bold text-[#5B0A14] tracking-tight">Manajemen Bahan Baku & Gudang</h1>
                <p class="text-xs text-stone-500 mt-0.5">Monitoring kuantitas bahan baku riil, restock nota faktur, dan audit mutasi stok resep.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="restockModalOpen = true"
                        class="px-3 py-1.5 rounded-[6px] bg-white border border-[#E7E0D8] text-stone-700 hover:bg-stone-50 font-semibold text-xs transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Restock Gudang</span>
                </button>

                <button type="button" 
                        @click="materialModalOpen = true"
                        class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wide transition shadow-subtle flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Daftarkan Bahan Baku</span>
                </button>
            </div>
        </div>

        <!-- Raw Materials Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse ($rawMaterials as $material)
                @php
                    $currentStock = (float) ($material->outletStocks->first()->current_stock ?? 0);
                    $isLow = $currentStock <= (float) $material->minimum_stock;
                @endphp
                <div class="bg-white rounded-[10px] p-4 border {{ $isLow ? 'border-amber-300 ring-1 ring-amber-200' : 'border-[#E7E0D8]' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 font-mono">{{ $material->unit }}</span>
                            <div class="flex items-center gap-1">
                                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold {{ $isLow ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-800' }}">
                                    {{ $isLow ? 'Menipis' : 'Aman' }}
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
                <table class="w-full text-left text-xs">
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

    </div>
</x-app-layout>
