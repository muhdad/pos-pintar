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
        categoryModalOpen: false,
        editCategoryModalOpen: false,
        editingCategory: { id: '', name: '', icon: '' },

        productModalOpen: false,
        isEditingProduct: false,
        productModalTitle: 'Tambah Menu Produk Baru',
        productFormAction: '{{ route('backoffice.products.store') }}',
        productId: null,
        productName: '',
        categoryId: '{{ $categories->first()->id ?? '' }}',
        sku: '',
        sellingPrice: '',
        cogs: '',
        imageUrl: '',
        isActive: true,
        recipes: [],

        openCreateProductModal() {
            this.isEditingProduct = false;
            this.productModalTitle = 'Tambah Menu Produk Baru';
            this.productFormAction = '{{ route('backoffice.products.store') }}';
            this.productId = null;
            this.productName = '';
            this.categoryId = '{{ $categories->first()->id ?? '' }}';
            this.sku = '';
            this.sellingPrice = '';
            this.cogs = '';
            this.imageUrl = '';
            this.isActive = true;
            this.recipes = [];
            this.productModalOpen = true;
        },

        openEditProductModal(product) {
            this.isEditingProduct = true;
            this.productModalTitle = 'Edit Menu Produk: ' + product.name;
            this.productFormAction = '/backoffice/products/' + product.id;
            this.productId = product.id;
            this.productName = product.name;
            this.categoryId = product.category_id;
            this.sku = product.sku || '';
            this.sellingPrice = Number(product.selling_price);
            this.cogs = Number(product.cogs);
            this.imageUrl = product.image_url || '';
            this.isActive = Boolean(product.is_active);
            this.recipes = (product.recipes || []).map(r => ({
                raw_material_id: r.raw_material_id,
                quantity: Number(r.quantity)
            }));
            this.productModalOpen = true;
        },

        openEditCategoryModal(cat) {
            this.editingCategory = { id: cat.id, name: cat.name, icon: cat.icon || 'soup' };
            this.editCategoryModalOpen = true;
        },

        addRecipeRow() {
            this.recipes.push({ raw_material_id: '', quantity: 1 });
        },
        removeRecipeRow(index) {
            this.recipes.splice(index, 1);
        }
    }" class="space-y-6">

        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-stone-200/60">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight">Katalog Produk & Resep (BOM)</h1>
                <p class="text-xs text-stone-500 mt-0.5">Kelola menu jual, kalkulasi otomatis HPP modal, foto makanan, dan resep bahan baku.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <!-- Tambah Kategori -->
                <button type="button" 
                        @click="categoryModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                    <span>+ Kategori Baru</span>
                </button>

                <!-- Tambah Produk -->
                <button type="button" 
                        @click="openCreateProductModal()"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs tracking-wide shadow-sm shadow-orange-500/20 transition flex items-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Menu Produk</span>
                </button>
            </div>
        </div>

        <!-- Category Management Bar -->
        <div class="bg-white rounded-2xl p-4 border border-stone-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-2 mb-3 border-b border-stone-100 text-xs">
                <span class="font-extrabold uppercase tracking-wider text-stone-400 text-[10px]">Daftar Kategori Menu</span>
                <span class="text-stone-500 text-[11px]">{{ $categories->count() }} Kategori Terdaftar</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @forelse ($categories as $cat)
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200 text-xs font-semibold text-stone-800 group hover:border-amber-300 transition">
                        <span>{{ $cat->name }}</span>
                        <span class="text-[10px] text-stone-400 font-mono">({{ $cat->products->count() ?? $products->where('category_id', $cat->id)->count() }})</span>
                        
                        <!-- Edit Category Button -->
                        <button type="button" 
                                @click="openEditCategoryModal({{ json_encode($cat) }})" 
                                title="Edit Kategori" 
                                class="p-1 text-stone-400 hover:text-amber-700 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>

                        <!-- Delete Category Button -->
                        <form method="POST" 
                              action="{{ route('backoffice.categories.destroy', $cat->id) }}" 
                              onsubmit="return confirm('Hapus kategori {{ $cat->name }}? Produk dalam kategori ini akan menjadi tanpa kategori.')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Kategori" class="p-1 text-stone-400 hover:text-rose-600 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <span class="text-xs text-stone-400 italic">Belum ada kategori. Silakan tambahkan kategori baru.</span>
                @endforelse
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-stone-400 font-bold border-b border-stone-100 bg-stone-50/60 uppercase text-[10px] tracking-wider">
                            <th class="py-3.5 px-6">Menu & Foto</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Harga Jual</th>
                            <th class="py-3.5 px-4">HPP (Modal)</th>
                            <th class="py-3.5 px-4">Margin Laba</th>
                            <th class="py-3.5 px-4">Komposisi Resep (BOM)</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($products as $product)
                            @php
                                $margin = $product->selling_price > 0 
                                    ? round((($product->selling_price - $product->cogs) / $product->selling_price) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 overflow-hidden flex items-center justify-center font-bold text-base shrink-0 shadow-2xs">
                                            @if ($product->image_url)
                                                <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xl">🍲</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-stone-900 leading-snug">{{ $product->name }}</h4>
                                            <span class="text-[10px] text-stone-400 font-mono">{{ $product->sku ?? 'SKU-REG' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-100 text-stone-700 border border-stone-200">
                                        {{ $product->category->name ?? 'Reguler' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-black text-stone-900 font-mono text-sm">
                                    Rp{{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-medium text-stone-600 font-mono">
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
                                                <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-900 border border-amber-200/80 text-[10px] font-medium">
                                                    {{ $recipe->rawMaterial->name ?? 'Bahan' }}: <strong>{{ (float) $recipe->quantity }}{{ $recipe->rawMaterial->unit ?? '' }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-stone-400 italic">Menu instan / tanpa resep</span>
                                    @endif
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-4 px-4 text-center">
                                    <form method="POST" action="{{ route('backoffice.products.toggle', $product->id) }}">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk ubah status aktif di POS" 
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold transition {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-stone-100 text-stone-500 border border-stone-200 hover:bg-stone-200' }}">
                                            {{ $product->is_active ? '● Aktif' : '○ Nonaktif' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Action Buttons (Edit & Delete) -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Product Button -->
                                        <button type="button" 
                                                @click="openEditProductModal({{ json_encode($product) }})"
                                                class="p-2 rounded-xl bg-stone-50 hover:bg-amber-100 text-stone-600 hover:text-amber-900 border border-stone-200 transition" 
                                                title="Edit Menu & Resep">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Product Form -->
                                        <form method="POST" 
                                              action="{{ route('backoffice.products.destroy', $product->id) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini dari katalog?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-stone-50 hover:bg-rose-50 text-stone-400 hover:text-rose-600 border border-stone-200 transition" title="Hapus Produk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-stone-400">
                                    <div class="w-12 h-12 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                        📋
                                    </div>
                                    <p class="text-xs font-semibold text-stone-700">Belum ada menu produk terdaftar.</p>
                                    <p class="text-[11px] text-stone-400 mt-0.5">Silakan tambahkan kategori dan produk baru menggunakan tombol di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div x-show="categoryModalOpen" 
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="categoryModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-sm font-bold text-stone-900">Tambah Kategori Baru</h3>
                    <button type="button" @click="categoryModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.categories.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Contoh: Soto, Mie Ayam, Minuman" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="categoryModalOpen = false" class="px-3.5 py-2 rounded-xl bg-stone-100 text-stone-600 text-xs font-bold hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-xs transition">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div x-show="editCategoryModalOpen" 
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="editCategoryModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-sm font-bold text-stone-900">Edit Kategori</h3>
                    <button type="button" @click="editCategoryModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <form :action="'/backoffice/categories/' + editingCategory.id" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Nama Kategori</label>
                        <input type="text" name="name" x-model="editingCategory.name" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="editCategoryModalOpen = false" class="px-3.5 py-2 rounded-xl bg-stone-100 text-stone-600 text-xs font-bold hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-xs transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create / Edit Product Modal -->
        <div x-show="productModalOpen" 
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-stone-200 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in duration-150"
                 @click.outside="productModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div>
                        <h3 class="text-base font-extrabold text-stone-900" x-text="productModalTitle"></h3>
                        <p class="text-xs text-stone-500">Konfigurasi menu jual, harga, foto produk, dan resep komposisi.</p>
                    </div>
                    <button type="button" @click="productModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                @if($categories->isEmpty())
                    <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        ⚠️ Anda belum memiliki kategori produk. Silakan klik <strong>"+ Kategori Baru"</strong> terlebih dahulu.
                    </div>
                @else
                    <form :action="productFormAction" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_method" :value="isEditingProduct ? 'PUT' : 'POST'">

                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   x-model="productName" 
                                   required 
                                   placeholder="Contoh: Soto Ayam Lamongan" 
                                   class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs text-stone-900 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                                <select name="category_id" 
                                        x-model="categoryId" 
                                        required 
                                        class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">SKU / Kode Menu</label>
                                <input type="text" 
                                       name="sku" 
                                       x-model="sku" 
                                       placeholder="Otomatis jika kosong" 
                                       class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" 
                                       name="selling_price" 
                                       x-model="sellingPrice" 
                                       required min="0" 
                                       placeholder="15000" 
                                       class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2 text-xs text-stone-900 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">HPP / Modal Satuan (Rp)</label>
                                <input type="number" 
                                       name="cogs" 
                                       x-model="cogs" 
                                       min="0" 
                                       placeholder="Otomatis dari resep BOM" 
                                       class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                            </div>
                        </div>

                        <!-- Foto Produk Selection & Quick Presets -->
                        <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 space-y-2">
                            <label class="block text-xs font-bold text-stone-700">Foto Menu Makanan (URL)</label>
                            <input type="text" 
                                   name="image_url" 
                                   x-model="imageUrl" 
                                   placeholder="Contoh: /images/products/soto-ayam.jpg" 
                                   class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs text-stone-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            
                            <div class="text-[10px] text-stone-500 font-bold uppercase tracking-wider">Pilih Cepat Foto SLL:</div>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="imageUrl = '/images/products/soto-ayam.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🍲 Soto Ayam</button>
                                <button type="button" @click="imageUrl = '/images/products/soto-daging.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🥩 Soto Daging</button>
                                <button type="button" @click="imageUrl = '/images/products/mie-ayam.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🍜 Mie Ayam</button>
                                <button type="button" @click="imageUrl = '/images/products/bakso.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🥣 Bakso</button>
                                <button type="button" @click="imageUrl = '/images/products/ceker.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🍗 Ceker</button>
                                <button type="button" @click="imageUrl = '/images/products/lauk.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🍳 Lauk & Telur</button>
                                <button type="button" @click="imageUrl = '/images/products/es-jeruk.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🍊 Es Jeruk</button>
                                <button type="button" @click="imageUrl = '/images/products/es-teh.jpg'" class="px-2 py-1 rounded-lg bg-white border border-stone-200 hover:border-amber-400 text-[10px] font-semibold text-stone-700">🍹 Es Teh</button>
                            </div>
                        </div>

                        <!-- Resep Komposisi Bahan Baku (BOM) -->
                        <div class="pt-2 border-t border-stone-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-stone-700">Komposisi Resep Bahan Baku (BOM)</label>
                                @if(!empty($rawMaterials) && $rawMaterials->isNotEmpty())
                                    <button type="button" @click="addRecipeRow()" class="text-[11px] font-bold text-amber-700 hover:text-amber-800">
                                        + Tambah Bahan Resep
                                    </button>
                                @endif
                            </div>

                            @if(empty($rawMaterials) || $rawMaterials->isEmpty())
                                <p class="text-[11px] text-stone-400 italic">Belum ada bahan baku di gudang. Menu dapat didaftarkan sebagai produk instan tanpa resep.</p>
                            @else
                                <div class="space-y-2">
                                    <template x-for="(item, idx) in recipes" :key="idx">
                                        <div class="flex items-center gap-2">
                                            <select :name="'recipes[' + idx + '][raw_material_id]'" 
                                                    x-model="item.raw_material_id" 
                                                    required 
                                                    class="flex-1 bg-stone-50 border border-stone-200 rounded-xl px-2.5 py-1.5 text-xs text-stone-800">
                                                <option value="">Pilih Bahan Baku...</option>
                                                @foreach($rawMaterials as $mat)
                                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" 
                                                   step="any" 
                                                   min="0.01" 
                                                   :name="'recipes[' + idx + '][quantity]'" 
                                                   x-model="item.quantity" 
                                                   placeholder="Takaran" 
                                                   required 
                                                   class="w-24 bg-stone-50 border border-stone-200 rounded-xl px-2.5 py-1.5 text-xs text-stone-900 font-mono">
                                            <button type="button" @click="removeRecipeRow(idx)" class="text-rose-500 hover:text-rose-700 p-1 font-bold">✕</button>
                                        </div>
                                    </template>
                                </div>
                            @endif
                        </div>

                        <!-- Active Checkbox -->
                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="product_is_active" 
                                   value="1" 
                                   x-model="isActive" 
                                   class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                            <label for="product_is_active" class="text-xs font-bold text-stone-700 cursor-pointer">
                                Produk Aktif & Tampil di Terminal Kasir POS
                            </label>
                        </div>

                        <div class="pt-3 border-t border-stone-100 flex justify-end gap-2.5">
                            <button type="button" @click="productModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold transition">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-xs transition">
                                <span x-text="isEditingProduct ? 'Simpan Perubahan' : 'Simpan Produk'"></span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>