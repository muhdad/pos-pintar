<x-app-layout>
    @section('page_title', 'Stok Bahan Baku & Gudang')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">✕</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold p-1">✕</button>
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
    }" class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-stone-200/60">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight">Manajemen Bahan Baku & Gudang</h1>
                <p class="text-xs text-stone-500 mt-0.5">Monitoring kuantitas bahan baku riil, restock nota faktur, dan audit mutasi stok resep.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" 
                        @click="restockModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                    <span>📥 Restock Gudang</span>
                </button>

                <button type="button" 
                        @click="materialModalOpen = true"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs tracking-wide shadow-sm shadow-orange-500/20 transition flex items-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Daftarkan Bahan Baku</span>
                </button>
            </div>
        </div>

        <!-- Raw Materials Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse ($rawMaterials as $material)
                @php
                    $currentStock = (float) ($material->outletStocks->first()->current_stock ?? 0);
                    $isLow = $currentStock <= (float) $material->minimum_stock;
                @endphp
                <div class="bg-white rounded-2xl p-5 border {{ $isLow ? 'border-amber-400 ring-2 ring-amber-100' : 'border-stone-200/80' }} shadow-xs flex flex-col justify-between hover:border-amber-300 transition group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 font-mono">{{ $material->unit }}</span>
                            <div class="flex items-center gap-1">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isLow ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $isLow ? '⚠️ Stok Menipis' : '✓ Normal' }}
                                </span>

                                <!-- Edit Material Button -->
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($material) }}, {{ $currentStock }})"
                                        class="p-1 rounded-lg text-stone-400 hover:text-amber-800 hover:bg-amber-50 transition" 
                                        title="Edit Bahan Baku">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <!-- Delete Material Button -->
                                <form method="POST" 
                                      action="{{ route('backoffice.inventory.raw-material.destroy', $material->id) }}" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus bahan baku {{ $material->name }}?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Bahan Baku">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <h3 class="text-sm font-extrabold text-stone-900 leading-snug">{{ $material->name }}</h3>
                        <p class="text-[11px] text-stone-400 mt-0.5">HPP: Rp{{ number_format($material->average_cost, 0, ',', '.') }}/{{ $material->unit }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-stone-100 flex items-end justify-between">
                        <div>
                            <span class="text-[10px] text-stone-500 block">Sisa Stok Riil</span>
                            <div class="text-xl font-black {{ $isLow ? 'text-amber-700' : 'text-stone-900' }} font-mono">
                                {{ number_format($currentStock, 0, ',', '.') }} <span class="text-xs font-normal text-stone-500 font-sans">{{ $material->unit }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] text-stone-400 font-mono">Min: {{ (float) $material->minimum_stock }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-stone-200/80 shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-2 text-xl">
                        📦
                    </div>
                    <p class="text-xs font-semibold text-stone-700">Belum ada bahan baku di gudang.</p>
                    <p class="text-[11px] text-stone-400 mt-0.5">Klik tombol "+ Daftarkan Bahan Baku" di atas untuk menambahkan bahan baku pertama Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Stock Movements (Kartu Stok) Audit Table -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-xs">
            <div class="pb-3 border-b border-stone-100 mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-stone-900">Kartu Mutasi Stok Terbaru</h3>
                    <p class="text-xs text-stone-500">Pencatatan otomatis pemotongan resep kasir, restock nota, dan penyesuaian opname.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-stone-400 font-bold border-b border-stone-100 uppercase tracking-wider text-[10px]">
                            <th class="pb-3">Waktu</th>
                            <th class="pb-3">Bahan Baku</th>
                            <th class="pb-3">Tipe Mutasi</th>
                            <th class="pb-3 text-right">Perubahan (Delta)</th>
                            <th class="pb-3">Keterangan / Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($movements as $mov)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-3 text-stone-500 font-mono text-[11px]">
                                    {{ $mov->created_at->format('d/m/Y H:i') }} WIB
                                </td>
                                <td class="py-3 font-bold text-stone-900">
                                    {{ $mov->outletStock->rawMaterial->name ?? 'Bahan' }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mov->type === 'IN' ? 'bg-emerald-100 text-emerald-800' : ($mov->type === 'OUT' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $mov->type === 'IN' ? 'Masuk (IN)' : ($mov->type === 'OUT' ? 'Keluar (OUT)' : 'Opname') }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-black {{ $mov->type === 'IN' ? 'text-emerald-600' : ($mov->type === 'OUT' ? 'text-rose-600' : 'text-amber-700') }} font-mono">
                                    {{ $mov->type === 'IN' ? '+' : ($mov->type === 'OUT' ? '-' : ($mov->quantity_delta >= 0 ? '+' : '')) }}{{ (float) $mov->quantity_delta }} {{ $mov->outletStock->rawMaterial->unit ?? '' }}
                                </td>
                                <td class="py-3 text-stone-600 text-[11px]">
                                    {{ $mov->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-stone-400">
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
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="materialModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-sm font-bold text-stone-900">Daftarkan Bahan Baku Baru</h3>
                    <button type="button" @click="materialModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.inventory.raw-material.store') }}" class="mt-4 space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Nama Bahan Baku <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Daging Ayam Suwir, Beras, Telur" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Satuan Takaran <span class="text-rose-500">*</span></label>
                            <select name="unit" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                                <option value="gram">gram (gr)</option>
                                <option value="ml">mililiter (ml)</option>
                                <option value="pcs">pcs / butir</option>
                                <option value="kg">kilogram (kg)</option>
                                <option value="liter">liter (lt)</option>
                                <option value="lembar">lembar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">HPP per Satuan (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="average_cost" required min="0" placeholder="Contoh: 45" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Ambang Min. Stok <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="minimum_stock" required min="0" placeholder="Contoh: 1000" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Stok Awal Saat Ini</label>
                            <input type="number" step="any" name="initial_stock" min="0" placeholder="0" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-stone-100 flex justify-end gap-2">
                        <button type="button" @click="materialModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-xs transition">Simpan Bahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Bahan Baku Modal -->
        <div x-show="editMaterialModalOpen" 
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="editMaterialModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-sm font-bold text-stone-900">Edit Data Bahan Baku</h3>
                    <button type="button" @click="editMaterialModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <form :action="'/backoffice/inventory/raw-material/' + editingMaterial.id" method="POST" class="mt-4 space-y-3.5">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Nama Bahan Baku <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editingMaterial.name" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Satuan Takaran <span class="text-rose-500">*</span></label>
                            <select name="unit" x-model="editingMaterial.unit" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                                <option value="gram">gram (gr)</option>
                                <option value="ml">mililiter (ml)</option>
                                <option value="pcs">pcs / butir</option>
                                <option value="kg">kilogram (kg)</option>
                                <option value="liter">liter (lt)</option>
                                <option value="lembar">lembar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">HPP per Satuan (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="average_cost" x-model="editingMaterial.average_cost" required min="0" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Ambang Min. Stok <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="minimum_stock" x-model="editingMaterial.minimum_stock" required min="0" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Koreksi Stok Riil (Gudang)</label>
                            <input type="number" step="any" name="current_stock" x-model="editingMaterial.current_stock" min="0" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-stone-100 flex justify-end gap-2">
                        <button type="button" @click="editMaterialModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-xs transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Restock Gudang Modal -->
        <div x-show="restockModalOpen" 
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="restockModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-sm font-bold text-stone-900">Restock Bahan Baku</h3>
                    <button type="button" @click="restockModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                @if($rawMaterials->isEmpty())
                    <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        ⚠️ Belum ada bahan baku terdaftar. Silakan klik <strong>"+ Daftarkan Bahan Baku"</strong> terlebih dahulu.
                    </div>
                @else
                    <form method="POST" action="{{ route('backoffice.inventory.restock') }}" class="mt-4 space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Pilih Bahan Baku <span class="text-rose-500">*</span></label>
                            <select name="raw_material_id" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                                @foreach($rawMaterials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->name }} (Satuan: {{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Jumlah Restock (Kuantitas) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="quantity" required min="0.01" placeholder="Contoh: 1000" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Catatan / No. Faktur Pembelian</label>
                            <input type="text" name="notes" placeholder="Contoh: Pembelian supplier Pasar Lamongan" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>

                        <div class="pt-3 border-t border-stone-100 flex justify-end gap-2">
                            <button type="button" @click="restockModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-xs font-bold hover:bg-stone-200 transition">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-bold shadow-xs transition">Tambah Stok</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>