<x-app-layout>
    @section('page_title', 'Katalog Produk & Resep (BOM)')

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
        categoryModalOpen: false,
        editCategoryModalOpen: false,
        editingCategory: { id: '', name: '', icon: '' },

        importModalOpen: false,
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
            this.productModalTitle = 'Edit Menu: ' + product.name;
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
        },

        formatRupiah(val) {
            if (val === null || val === undefined || val === '') return '';
            const num = String(val).replace(/\D/g, '');
            return num ? Number(num).toLocaleString('id-ID') : '';
        },
        parseRupiah(val) {
            const clean = String(val).replace(/\D/g, '');
            return clean ? parseInt(clean, 10) : '';
        }
    }" class="space-y-5">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200/80">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">Katalog Menu & Resep (BOM)</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola harga jual, perhitungan HPP resep otomatis, foto menu, dan kategori.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Export JSON Button -->
                <a href="{{ route('backoffice.catalog.export') }}" 
                   class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs"
                   title="Download file data menu & bahan baku (JSON)">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export</span>
                </a>

                <!-- Import JSON Button -->
                <button type="button" 
                        @click="importModalOpen = true"
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs"
                        title="Upload file data menu & bahan baku (JSON)">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import</span>
                </button>

                <!-- Kosongkan Data Dummy Button -->
                <form method="POST" action="{{ route('backoffice.reset-dummy-data') }}" onsubmit="return confirm('PERINGATAN: Seluruh data dummy (produk, transaksi, bahan baku) akan dibersihkan untuk persiapan input data riil.\n\nApakah Anda yakin ingin melanjutkan?')">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-semibold text-xs transition flex items-center gap-1.5"
                            title="Kosongkan seluruh data dummy">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Kosongkan Dummy</span>
                    </button>
                </form>

                <!-- Export Excel Menu & HPP Button -->
                <a href="{{ route('backoffice.products.export-excel') }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs"
                   title="Export Seluruh Menu & HPP Resep ke File Excel">
                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel (.xls)</span>
                </a>

                <!-- Tambah Kategori Button -->
                <button type="button" 
                        @click="categoryModalOpen = true"
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition shadow-xs">
                    Kategori Baru
                </button>

                <!-- Tambah Produk Button -->
                <button type="button" 
                        @click="openCreateProductModal()"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs tracking-wide transition shadow-xs flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Menu</span>
                </button>
            </div>
        </div>

        <!-- Category Management Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-stone-100 text-xs">
                <span class="font-bold uppercase tracking-wider text-stone-400 text-[10px]">Kategori Terdaftar</span>
                <span class="text-stone-500 text-[11px] font-mono">{{ $categories->count() }} Kategori</span>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                @forelse ($categories as $cat)
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] bg-stone-50 border border-stone-200 text-xs text-stone-800">
                        <span class="font-semibold">{{ $cat->name }}</span>
                        <span class="text-[10px] text-stone-400 font-mono">({{ $cat->products->count() ?? $products->where('category_id', $cat->id)->count() }})</span>
                        
                        <!-- Edit Button -->
                        <button type="button" 
                                @click="openEditCategoryModal({{ json_encode($cat) }})" 
                                title="Edit Kategori" 
                                class="text-stone-400 hover:text-stone-800 p-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>

                        <!-- Delete Button -->
                        <form method="POST" 
                              action="{{ route('backoffice.categories.destroy', $cat->id) }}" 
                              onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Kategori" class="text-stone-400 hover:text-red-600 p-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <span class="text-xs text-stone-400 italic">Belum ada kategori terdaftar.</span>
                @endforelse
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="bg-white rounded-[10px] border border-[#E7E0D8] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-xs">
                    <thead>
                        <tr class="text-stone-500 font-bold border-b border-[#E7E0D8] bg-stone-50 uppercase text-[10px] tracking-wider sticky top-0 z-10">
                            <th class="py-2.5 px-4">Menu</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3 text-right">Harga Jual</th>
                            <th class="py-2.5 px-3 text-right">HPP Modal</th>
                            <th class="py-2.5 px-3 text-center">Margin</th>
                            <th class="py-2.5 px-3">Komposisi Resep (BOM)</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($products as $product)
                            @php
                                $margin = $product->selling_price > 0 
                                    ? round((($product->selling_price - $product->cogs) / $product->selling_price) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-3 px-4">
                                    <div>
                                        <span class="font-bold text-stone-900 block leading-tight">{{ $product->name }}</span>
                                        <span class="text-[10px] text-stone-400 font-mono">{{ $product->sku ?? 'SKU-REG' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-semibold bg-stone-100 text-stone-700 border border-stone-200">
                                        {{ $product->category->name ?? 'Reguler' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums font-bold text-stone-900">
                                    Rp{{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums text-stone-600">
                                    Rp{{ number_format($product->cogs, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-center font-mono tabular-nums">
                                    <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold {{ $margin >= 50 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ $margin }}%
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    @if ($product->recipes->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach ($product->recipes as $recipe)
                                                <span class="px-1.5 py-0.5 rounded-[4px] bg-stone-50 border border-stone-200 text-[10px] font-mono text-stone-700">
                                                    {{ $recipe->rawMaterial->name ?? 'Bahan' }}: {{ (float) $recipe->quantity }}{{ $recipe->rawMaterial->unit ?? '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-stone-400 italic">Menu instan / tanpa resep</span>
                                    @endif
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-3 px-3 text-center">
                                    <form method="POST" action="{{ route('backoffice.products.toggle', $product->id) }}">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk ubah status tampil di kasir" 
                                                class="px-2 py-0.5 rounded-[4px] text-[10px] font-bold transition {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-stone-100 text-stone-500 border border-stone-200 hover:bg-stone-200' }}">
                                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" 
                                                @click="openEditProductModal({{ json_encode($product) }})"
                                                class="p-1.5 rounded-[4px] bg-stone-50 hover:bg-stone-100 text-stone-600 border border-stone-200 transition" 
                                                title="Edit Menu & Resep">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>

                                        <form method="POST" 
                                              action="{{ route('backoffice.products.destroy', $product->id) }}" 
                                              onsubmit="return confirm('Hapus menu {{ $product->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-[4px] bg-stone-50 hover:bg-red-50 text-stone-400 hover:text-red-600 border border-stone-200 transition" title="Hapus Produk">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-stone-400">
                                    Belum ada menu produk terdaftar. Klik "+ Tambah Menu" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div x-show="categoryModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="categoryModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Tambah Kategori</h3>
                    <button type="button" @click="categoryModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.categories.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Contoh: Soto, Mie Ayam, Minuman" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="categoryModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div x-show="editCategoryModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="editCategoryModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Edit Kategori</h3>
                    <button type="button" @click="editCategoryModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <form :action="'/backoffice/categories/' + editingCategory.id" method="POST" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Kategori</label>
                        <input type="text" name="name" x-model="editingCategory.name" required class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="editCategoryModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create / Edit Product Modal -->
        <div x-show="productModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-xl w-full p-5 border border-[#E7E0D8] shadow-modal max-h-[90vh] overflow-y-auto animate-in fade-in duration-150"
                 @click.outside="productModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div>
                        <h3 class="text-sm font-display font-bold text-stone-900" x-text="productModalTitle"></h3>
                        <p class="text-xs text-stone-500">Konfigurasi menu jual, harga, foto produk, dan resep komposisi.</p>
                    </div>
                    <button type="button" @click="productModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                @if($categories->isEmpty())
                    <div class="mt-3 p-3 rounded-[6px] bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        Silakan tambahkan minimal satu kategori menu terlebih dahulu.
                    </div>
                @else
                    <form :action="productFormAction" method="POST" class="mt-4 space-y-3.5">
                        @csrf
                        <input type="hidden" name="_method" :value="isEditingProduct ? 'PUT' : 'POST'">

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   x-model="productName" 
                                   required 
                                   placeholder="Contoh: Soto Ayam Campur Nasi" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                                <select name="category_id" 
                                        x-model="categoryId" 
                                        required 
                                        class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1">SKU / Kode Menu</label>
                                <input type="text" 
                                       name="sku" 
                                       x-model="sku" 
                                       placeholder="Otomatis jika kosong" 
                                       class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-1.5 text-xs text-stone-900 font-mono focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1.5 text-stone-400 font-mono text-xs font-bold">Rp</span>
                                    <input type="text" 
                                           inputmode="numeric"
                                           :value="formatRupiah(sellingPrice)" 
                                           @input="sellingPrice = parseRupiah($event.target.value)"
                                           required 
                                           placeholder="15.000" 
                                           class="w-full bg-white border border-[#E7E0D8] rounded-[6px] pl-8 pr-3 py-1.5 text-xs text-stone-900 font-mono tabular-nums font-bold focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                </div>
                                <input type="hidden" name="selling_price" :value="sellingPrice">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1">HPP / Modal Satuan (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1.5 text-stone-400 font-mono text-xs font-bold">Rp</span>
                                    <input type="text" 
                                           inputmode="numeric"
                                           :value="formatRupiah(cogs)" 
                                           @input="cogs = parseRupiah($event.target.value)"
                                           placeholder="0" 
                                           class="w-full bg-white border border-[#E7E0D8] rounded-[6px] pl-8 pr-3 py-1.5 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                </div>
                                <input type="hidden" name="cogs" :value="cogs">
                            </div>
                        </div>

                        <!-- Resep Komposisi Bahan Baku (BOM) -->
                        <div class="pt-2 border-t border-stone-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-stone-700">Komposisi Resep (BOM)</label>
                                @if(!empty($rawMaterials) && $rawMaterials->isNotEmpty())
                                    <button type="button" @click="addRecipeRow()" class="text-[11px] font-bold text-[#C2410C] hover:underline">
                                        + Tambah Bahan Resep
                                    </button>
                                @endif
                            </div>

                            @if(empty($rawMaterials) || $rawMaterials->isEmpty())
                                <p class="text-[11px] text-stone-400 italic">Belum ada bahan baku di gudang. Menu didaftarkan sebagai menu instan tanpa resep.</p>
                            @else
                                <div class="space-y-1.5">
                                    <template x-for="(item, idx) in recipes" :key="idx">
                                        <div class="flex items-center gap-2">
                                            <select :name="'recipes[' + idx + '][raw_material_id]'" 
                                                    x-model="item.raw_material_id" 
                                                    required 
                                                    class="flex-1 bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1 text-xs text-stone-800 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
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
                                                   class="w-24 bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1 text-xs text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                            <button type="button" @click="removeRecipeRow(idx)" class="text-red-500 hover:text-red-700 p-1 font-bold">✕</button>
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
                                   class="rounded-[4px] border-[#E7E0D8] text-[#EA580C] focus:ring-[#EA580C]">
                            <label for="product_is_active" class="text-xs font-semibold text-stone-700 cursor-pointer">
                                Tampilkan di Terminal Kasir POS
                            </label>
                        </div>

                        <div class="pt-3 border-t border-stone-100 flex justify-end gap-2">
                            <button type="button" @click="productModalOpen = false" class="px-3.5 py-1.5 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium transition">Batal</button>
                            <button type="submit" class="px-4 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">
                                <span x-text="isEditingProduct ? 'Simpan Perubahan' : 'Simpan Menu'"></span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Import Katalog JSON Modal -->
        <div x-show="importModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="importModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Import Data Katalog & Bahan</h3>
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
                            <li>Kategori Produk</li>
                            <li>Bahan Baku & Saldo Stok Gudang</li>
                            <li>Daftar Menu & Resep (BOM)</li>
                        </ul>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="importModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
