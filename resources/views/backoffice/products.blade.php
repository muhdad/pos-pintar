<x-app-layout>
    @section('page_title', 'Katalog Produk & Resep (BOM)')

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

    <div x-data="{
        categoryModalOpen: false,
        productModalOpen: false,
        recipes: [],
        addRecipeRow() {
            this.recipes.push({ raw_material_id: '', quantity: 1 });
        },
        removeRecipeRow(index) {
            this.recipes.splice(index, 1);
        }
    }" class="space-y-6">

        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/60">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Katalog Produk & Resep (BOM)</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola menu jual, kalkulasi otomatis HPP modal, dan resep komposisi bahan baku.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <!-- Tambah Kategori -->
                <button type="button" 
                        @click="categoryModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                    <span>+ Kategori Baru</span>
                </button>

                <!-- Tambah Produk -->
                <button type="button" 
                        @click="productModalOpen = true; recipes = []"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs tracking-wide shadow-sm shadow-orange-500/20 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Menu Produk</span>
                </button>
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold border-b border-slate-100 bg-slate-50/50 uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-6">Menu & SKU</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Harga Jual</th>
                            <th class="py-3 px-4">HPP (Modal)</th>
                            <th class="py-3 px-4">Margin Laba</th>
                            <th class="py-3 px-4">Komposisi Resep (BOM)</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $product)
                            @php
                                $margin = $product->selling_price > 0 
                                    ? round((($product->selling_price - $product->cogs) / $product->selling_price) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 overflow-hidden flex items-center justify-center font-bold text-base shrink-0 shadow-2xs">
                                            @if ($product->image_url)
                                                <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-lg">🍲</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900">{{ $product->name }}</h4>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $product->sku ?? 'SKU-REG' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $product->category->name ?? 'Reguler' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-black text-slate-900 font-mono text-sm">
                                    Rp{{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-medium text-slate-600 font-mono">
                                    Rp{{ number_format($product->cogs, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $margin >= 50 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $margin }}%
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($product->recipes->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach ($product->recipes as $recipe)
                                                <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-medium">
                                                    {{ $recipe->rawMaterial->name ?? 'Bahan' }}: <strong>{{ (float) $recipe->quantity }}{{ $recipe->rawMaterial->unit ?? '' }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Menu instan / tanpa resep</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <form method="POST" 
                                          action="{{ route('backoffice.products.destroy', $product->id) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini dari katalog?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                        📋
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Belum ada menu produk terdaftar.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Silakan tambahkan kategori dan produk baru menggunakan tombol di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category Modal -->
        <div x-show="categoryModalOpen" 
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200"
                 @click.outside="categoryModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Tambah Kategori Baru</h3>
                    <button type="button" @click="categoryModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.categories.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Contoh: Coffee, Pastry, Makanan Berat" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="categoryModalOpen = false" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Modal -->
        <div x-show="productModalOpen" 
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto"
                 @click.outside="productModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Tambah Menu Produk Baru</h3>
                    <button type="button" @click="productModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                @if($categories->isEmpty())
                    <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        ⚠️ Anda belum memiliki kategori produk. Silakan klik <strong>"+ Kategori Baru"</strong> terlebih dahulu.
                    </div>
                @else
                    <form method="POST" action="{{ route('backoffice.products.store') }}" class="mt-4 space-y-3.5">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Kopi Susu Aren" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                                <select name="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">SKU (Barcode Opsional)</label>
                                <input type="text" name="sku" placeholder="Otomatis jika kosong" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="selling_price" required min="0" placeholder="25000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">HPP / Modal Satuan (Rp)</label>
                                <input type="number" name="cogs" min="0" placeholder="Otomatis dari Resep" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Resep Komposisi Bahan Baku (BOM) -->
                        <div class="pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-700">Komposisi Resep (BOM)</label>
                                @if(!empty($rawMaterials) && $rawMaterials->isNotEmpty())
                                    <button type="button" @click="addRecipeRow()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800">
                                        + Tambah Bahan Resep
                                    </button>
                                @endif
                            </div>

                            @if(empty($rawMaterials) || $rawMaterials->isEmpty())
                                <p class="text-[11px] text-slate-400 italic">Belum ada bahan baku di gudang. Menu dapat didaftarkan sebagai produk instan tanpa resep.</p>
                            @else
                                <div class="space-y-2">
                                    <template x-for="(item, idx) in recipes" :key="idx">
                                        <div class="flex items-center gap-2">
                                            <select :name="'recipes[' + idx + '][raw_material_id]'" required class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs text-slate-800">
                                                <option value="">Pilih Bahan Baku...</option>
                                                @foreach($rawMaterials as $mat)
                                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="any" min="0.01" :name="'recipes[' + idx + '][quantity]'" x-model="item.quantity" placeholder="Takaran" required class="w-24 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs text-slate-900 font-mono">
                                            <button type="button" @click="removeRecipeRow(idx)" class="text-rose-500 hover:text-rose-700 p-1 font-bold">✕</button>
                                        </div>
                                    </template>
                                </div>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" @click="productModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Simpan Produk</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
