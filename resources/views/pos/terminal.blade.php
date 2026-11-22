<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SLL POS - Kasir Soto Lamongan Lestari</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        window.POS_CONFIG = {
            tenantId: "{{ $tenant->id ?? '' }}",
            outletId: "{{ $outlet->id ?? '' }}",
            outletQrisPayload: @json($outlet->qris_payload ?? ''),
            outletQrisMerchantName: @json($outlet->qris_merchant_name ?? $outlet->name ?? 'SOTO LAMONGAN LESTARI'),
            outletQrisCity: @json($outlet->qris_city ?? 'JAKARTA'),
            userId: "{{ auth()->id() ?? '1' }}",
            shiftId: "{{ $shift->id ?? '' }}",
            activeShift: @json($shift ? ['id' => $shift->id, 'start_cash' => (float)$shift->start_cash, 'start_time' => $shift->start_time] : null)
        };
        window.POS_INITIAL_CATEGORIES = @json($categories ?? []);
        window.POS_INITIAL_PRODUCTS = @json($products ?? []);
        window.POS_INITIAL_STOCKS = @json($stocks ?? []);
        window.POS_INITIAL_TABLES = @json($tables ?? []);
        window.POS_INITIAL_OPEN_BILLS = @json($activeOpenBills ?? []);
    </script>
</head>
<body class="h-full antialiased font-sans bg-[#F6F4ED] text-[#121212] select-none overflow-hidden"
      x-data="posTerminal()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col overflow-hidden">
        <!-- 1. Top Bar: SLL Bauhaus Brand Rail with Shift Controls & Open Bills (Responsive) -->
        <header class="h-12 sm:h-13 bg-[#121212] border-b-2 border-stone-800 px-3 sm:px-4 flex items-center justify-between shrink-0 text-white z-30">
            <!-- Brand & Outlet Info -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#1D4ED8] border-2 border-[#FACC15] text-[#FACC15] flex items-center justify-center font-display font-black text-xs shrink-0 tracking-wider shadow-[2px_2px_0px_#DC2626]">
                    SLL
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="font-display font-black text-xs tracking-wider text-white leading-tight truncate uppercase">SOTO LAMONGAN LESTARI</span>
                        <span class="hidden sm:inline-block text-[9px] font-black px-1.5 py-0.5 border border-[#121212] bg-[#FACC15] text-[#121212] uppercase tracking-wider font-mono shrink-0">KASIR</span>
                    </div>
                    <div class="text-[10px] sm:text-[11px] text-stone-300 font-mono truncate flex items-center gap-1">
                        <span>{{ $outlet->name ?? 'Cabang Utama' }}</span>
                        <span class="text-stone-500">•</span>
                        <template x-if="activeShift">
                            <span class="text-emerald-400 font-bold">Shift Aktif (Rp<span x-text="Number(activeShift.start_cash || 0).toLocaleString('id-ID')"></span>)</span>
                        </template>
                        <template x-if="!activeShift">
                            <span class="text-[#FACC15] font-semibold cursor-pointer underline" @click="startShiftModalOpen = true">Buka Shift Kasir</span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <!-- Open Bills Counter Badge -->
                <button type="button" 
                        @click="openBillsModalOpen = true"
                        class="px-2.5 py-1 text-xs font-semibold rounded-[6px] transition flex items-center gap-1.5 border"
                        :class="openBills.length > 0 ? 'bg-[#FACC15] border-[#121212] text-[#121212] font-black' : 'bg-stone-900 border-stone-700 text-stone-300 hover:text-white'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Tagihan (Hold)</span>
                    <span class="px-1.5 py-0.2 rounded-none text-[10px] font-black font-mono border border-black"
                          :class="openBills.length > 0 ? 'bg-[#1D4ED8] text-white' : 'bg-stone-800 text-stone-300'"
                          x-text="openBills.length"></span>
                </button>

                <!-- Shift Management Actions -->
                <template x-if="activeShift">
                    <div class="hidden md:flex items-center gap-1.5">
                        <button type="button" 
                                @click="pettyCashModalOpen = true"
                                class="px-2.5 py-1 text-xs font-medium text-stone-200 bg-[#7A0F1C] hover:bg-[#911322] border border-[#941525] rounded-[6px] transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Kas Keluar</span>
                        </button>

                        <button type="button" 
                                @click="closeShiftModalOpen = true"
                                class="px-2.5 py-1 text-xs font-medium text-stone-200 bg-[#7A0F1C] hover:bg-[#911322] border border-[#941525] rounded-[6px] transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Tutup Shift</span>
                        </button>
                    </div>
                </template>

                <template x-if="!activeShift">
                    <button type="button" 
                            @click="startShiftModalOpen = true"
                            class="px-2.5 py-1 text-xs font-bold text-amber-900 bg-amber-400 hover:bg-amber-300 rounded-[6px] transition">
                        Buka Shift
                    </button>
                </template>

                <!-- Status Koneksi -->
                <div class="flex items-center gap-1.5 px-2 py-1 rounded-[6px] text-xs font-semibold"
                     :class="isOnline ? 'bg-emerald-950 border border-emerald-800 text-emerald-400' : 'bg-amber-950 border border-amber-800 text-amber-400'"
                     :title="isOnline ? 'Terhubung ke cloud backend' : 'Mode offline lokal'">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                    <span class="hidden sm:inline" x-text="isOnline ? (isSyncing ? 'Sinkron...' : 'Online') : 'Offline'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-2 sm:px-2.5 py-1.5 text-xs font-medium text-stone-200 bg-[#7A0F1C] hover:bg-[#911322] border border-[#941525] rounded-[6px] flex items-center gap-1.5 transition active:translate-y-px"
                        title="Hubungkan Printer Thermal Bluetooth">
                    <svg class="w-3.5 h-3.5 text-[#FFB800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span class="hidden lg:inline">Printer Thermal</span>
                </button>

                <!-- Backoffice Link -->
                <a href="{{ route('dashboard') }}" class="bauhaus-btn-blue text-xs">
                    <span>Backoffice</span>
                </a>
            </div>
        </header>

        <!-- 2. Workspace: Catalog Center + Pinned Order Panel Right (>= lg) -->
        <main class="flex-1 flex overflow-hidden relative">
            <!-- Center: Product Catalog -->
            <div class="flex-1 w-full flex flex-col p-2.5 sm:p-4 overflow-hidden bg-[#F6F4ED]">
                
                <!-- Search & Category Header -->
                <div class="shrink-0 space-y-2 mb-2.5 sm:mb-3">
                    <!-- Search Bar (F3) -->
                    <div class="relative">
                        <input type="text" 
                                id="pos-search-input"
                                x-model="searchQuery" 
                                placeholder="Cari menu soto, mie ayam, bakso, minuman... (F3 / /)" 
                                class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3.5 py-2 pl-9 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:ring-1 focus:ring-[#EA580C] focus:border-[#EA580C] transition">
                        <svg class="w-4 h-4 text-stone-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <template x-if="searchQuery">
                            <button type="button" @click="searchQuery = ''" class="absolute right-3 top-2 text-stone-400 hover:text-stone-700 text-xs font-bold">✕</button>
                        </template>
                    </div>

                    <!-- Category Chips -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none touch-pan-x">
                        <button type="button"
                                @click="activeCategory = 'ALL'"
                                class="px-3 py-1.5 rounded-[6px] text-xs font-semibold whitespace-nowrap transition"
                                :class="activeCategory === 'ALL' ? 'bg-[#5B0A14] text-white font-bold' : 'bg-white text-stone-700 border border-[#E7E0D8] hover:bg-stone-50'">
                            Semua Menu
                        </button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button"
                                    @click="activeCategory = cat.id"
                                    class="px-3 py-1.5 rounded-[6px] text-xs font-semibold whitespace-nowrap transition"
                                    :class="activeCategory === cat.id ? 'bg-[#5B0A14] text-white font-bold' : 'bg-white text-stone-700 border border-[#E7E0D8] hover:bg-stone-50'"
                                    x-text="cat.name">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Product Tiles Grid: Text-Only Minimalist POS Tiles (Fast, Clear, High Density, Out-Of-Stock Protection) -->
                <div class="flex-1 overflow-y-auto min-h-0 content-start auto-rows-max grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-2.5 p-0.5 pb-24 lg:pb-8">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="rounded-[8px] p-2.5 sm:p-3 flex flex-col justify-between transition group min-h-[96px] sm:min-h-[104px] relative select-none cursor-pointer"
                             :class="isProductOutOfStock(product) 
                                ? 'bg-amber-50/50 border border-amber-300 hover:border-amber-500' 
                                : 'bg-white border border-[#E7E0D8] hover:border-[#EA580C] hover:bg-[#FFFDF9] active:scale-[0.98] hover:shadow-subtle'">
                            
                            <div>
                                <!-- SKU / Category & Stock Status -->
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[9px] font-mono font-bold uppercase tracking-wider text-stone-400 truncate" x-text="product.sku || 'Menu SLL'"></span>
                                    <template x-if="isProductOutOfStock(product)">
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-[4px] bg-red-100 text-red-700 uppercase" :title="getOutOfStockReason(product)">Habis</span>
                                    </template>
                                    <template x-if="!isProductOutOfStock(product)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#EA580C]/40 group-hover:bg-[#EA580C] transition shrink-0"></span>
                                    </template>
                                </div>

                                <!-- Product Name -->
                                <h3 class="text-xs sm:text-sm font-bold text-stone-900 group-hover:text-[#5B0A14] line-clamp-2 leading-tight" x-text="product.name"></h3>
                            </div>

                            <!-- Price & Add Button (Reflects Ojol Multi-Pricing automatically) -->
                            <div class="mt-2 pt-1.5 border-t border-stone-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs sm:text-sm font-bold text-[#C2410C] font-mono tabular-nums block">
                                        Rp<span x-text="Number(getProductPrice(product)).toLocaleString('id-ID')"></span>
                                    </span>
                                    <template x-if="priceMultiplier !== 1.0">
                                        <span class="text-[9px] text-emerald-700 font-semibold block leading-none">Harga Ojol (+20%)</span>
                                    </template>
                                </div>
                                <button type="button" 
                                        class="w-6 h-6 sm:w-7 sm:h-7 rounded-[4px] flex items-center justify-center text-xs sm:text-sm font-bold transition active:translate-y-px shrink-0"
                                        :class="isProductOutOfStock(product) 
                                            ? 'bg-amber-100 text-amber-900 group-hover:bg-[#EA580C] group-hover:text-white' 
                                            : 'bg-stone-100 text-stone-700 group-hover:bg-[#EA580C] group-hover:text-white'"
                                        title="Tambah item">
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- 3. Desktop & Tablet Landscape: Pinned Order Panel (>= lg) -->
            <div class="hidden lg:flex lg:w-80 xl:w-90 2xl:w-96 bg-white border-l border-[#E7E0D8] flex-col h-full shrink-0 z-20">
                <!-- Panel Header -->
                <div class="p-3 border-b border-[#E7E0D8] bg-[#FAF6F0]/60 space-y-2 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <h2 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Pesanan Saat Ini</h2>
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-[4px] bg-stone-200 text-stone-700 font-mono" x-text="`${cart.length} item`"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="holdCurrentOrder()" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-amber-800 hover:text-amber-900 font-bold px-2 py-0.5 rounded-[4px] bg-amber-100 hover:bg-amber-200 transition"
                                    title="Simpan pesanan makan dulu bayar belakangan (F4)">
                                Hold [F4]
                            </button>
                            <button type="button" 
                                    @click="cart = []; currentOpenBillId = null; selectedTableId = ''; selectedTableName = ''" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-red-600 hover:text-red-700 font-semibold px-2 py-0.5 rounded-[4px] hover:bg-red-50 transition">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Order Type Selector (Dine-In, Bungkus, GoFood, Grab, Shopee) -->
                    <div class="grid grid-cols-5 gap-1 text-[10px] font-semibold">
                        <button type="button"
                                @click="setOrderType('DINE_IN')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'DINE_IN' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Dine In
                        </button>
                        <button type="button"
                                @click="setOrderType('TAKE_AWAY')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Bungkus
                        </button>
                        <button type="button"
                                @click="setOrderType('GOFOOD')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-700 text-white border-emerald-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            GoFood
                        </button>
                        <button type="button"
                                @click="setOrderType('GRABFOOD')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-700 text-white border-green-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Grab
                        </button>
                        <button type="button"
                                @click="setOrderType('SHOPEEFOOD')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-700 text-white border-orange-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Shopee
                        </button>
                    </div>

                    <!-- Dine In Table Selector (Meja 01 - Meja 12) -->
                    <template x-if="orderType === 'DINE_IN'">
                        <div class="pt-1 border-t border-stone-200">
                            <label class="block text-[10px] font-bold text-stone-600 uppercase mb-1">Pilih Meja Resto</label>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-1 max-h-20 overflow-y-auto">
                                <template x-for="tbl in tables" :key="tbl.id">
                                    <button type="button"
                                            @click="selectTable(tbl)"
                                            class="py-1 px-1 rounded-[4px] text-[10px] font-mono font-bold text-center border transition"
                                            :class="selectedTableId === tbl.id 
                                                ? 'bg-[#EA580C] text-white border-[#EA580C]' 
                                                : (tbl.is_occupied ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-white text-stone-700 border-stone-200 hover:bg-stone-50')"
                                            x-text="tbl.name">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2 min-h-0 bg-white">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-stone-400">
                            <p class="text-xs font-semibold text-stone-500">Belum ada item pesanan</p>
                            <p class="text-[11px] text-stone-400 mt-0.5">Pilih menu dari katalog di sebelah kiri.</p>
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="item.product_id">
                        <div class="bg-white rounded-[6px] p-2.5 border border-[#E7E0D8] space-y-1.5">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-stone-900 truncate" x-text="item.name"></h4>
                                    <span class="text-[10px] font-mono tabular-nums text-stone-500">
                                        @Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-stone-900 font-mono tabular-nums">
                                    Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <!-- Stepper & Notes Row -->
                            <div class="pt-1 border-t border-stone-100 flex items-center justify-between gap-2">
                                <input type="text" 
                                       x-model="item.notes" 
                                       placeholder="Catatan: kuah pisah, koya banyak..." 
                                       class="flex-1 text-[10px] bg-stone-50 border border-stone-200 rounded-[4px] px-2 py-0.5 placeholder:text-stone-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#EA580C]">

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" 
                                            @click="updateQuantity(idx, -1)" 
                                            class="w-7 h-7 rounded-[4px] bg-stone-100 hover:bg-stone-200 border border-stone-200 text-stone-700 flex items-center justify-center font-bold text-xs transition active:translate-y-px">
                                        -
                                    </button>
                                    <span class="text-xs font-bold text-stone-900 w-5 text-center font-mono tabular-nums" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQuantity(idx, 1)" 
                                            class="w-7 h-7 rounded-[4px] bg-stone-100 hover:bg-stone-200 border border-stone-200 text-stone-700 flex items-center justify-center font-bold text-xs transition active:translate-y-px">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Totals & Checkout / Hold / KOT Buttons -->
                <div class="p-3 border-t border-[#E7E0D8] bg-[#FAF6F0] space-y-1.5 shrink-0">
                    <div class="flex justify-between text-xs text-stone-600">
                        <span>Subtotal</span>
                        <span class="font-mono tabular-nums font-semibold">Rp<span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-stone-600">
                        <span>PPN (11%)</span>
                        <span class="font-mono tabular-nums font-semibold">Rp<span x-text="Number(taxAmount).toLocaleString('id-ID')"></span></span>
                    </div>
                    
                    <!-- Total Bayar Block -->
                    <div class="p-2.5 rounded-[6px] bg-[#5B0A14] text-white flex justify-between items-center mt-1">
                        <div>
                            <span class="text-[9px] text-[#FFB800] uppercase tracking-wider font-bold block">
                                Total Tagihan <span x-show="selectedTableName" x-text="`(${selectedTableName})`"></span>
                            </span>
                            <span class="text-base font-display font-black text-white font-mono tabular-nums leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-white/10 text-stone-200" x-text="orderType"></span>
                    </div>

                    <!-- Action Grid: KOT & Hold Order -->
                    <div class="grid grid-cols-2 gap-1.5 pt-1">
                        <button type="button"
                                @click="printKitchenTicket()"
                                :disabled="cart.length === 0"
                                class="h-9 rounded-[6px] bg-white border border-[#E7E0D8] hover:bg-stone-50 disabled:opacity-40 text-stone-700 font-bold text-[11px] transition flex items-center justify-center gap-1 active:translate-y-px">
                            <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Tiket Dapur (KOT)</span>
                        </button>

                        <button type="button"
                                @click="holdCurrentOrder()"
                                :disabled="cart.length === 0 || isHoldingOrder"
                                class="h-9 rounded-[6px] bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-stone-900 font-bold text-[11px] transition flex items-center justify-center gap-1 active:translate-y-px">
                            <span x-text="isHoldingOrder ? 'Menyimpan...' : 'Hold Bill (F4)'"></span>
                        </button>
                    </div>

                    <!-- Dominant Bayar Button (F2) -->
                    <button type="button"
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0"
                            class="w-full h-11 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center justify-center gap-1.5 active:translate-y-px mt-1.5">
                        <span>Bayar Sekarang</span>
                        <span class="text-[10px] bg-black/20 px-1 py-0.5 rounded font-mono font-normal">F2</span>
                    </button>
                </div>
            </div>
        </main>

        <!-- 4. Mobile / Tablet Portrait: Floating Bottom Bar (< lg) -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-[#E7E0D8] px-3.5 py-2.5 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] z-40 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0 cursor-pointer" @click="mobileCartOpen = true">
                <div class="w-10 h-10 rounded-[6px] bg-[#5B0A14] text-white flex items-center justify-center shrink-0 relative">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span x-show="cart.length > 0" 
                          class="absolute -top-1.5 -right-1.5 bg-[#EA580C] text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-4 text-center border-2 border-white font-mono" 
                          x-text="cart.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)"></span>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block" x-text="`${cart.length} item • ${orderType}`"></span>
                    <span class="text-sm font-display font-black text-[#5B0A14] font-mono tabular-nums block truncate">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        @click="mobileCartOpen = true"
                        class="px-3.5 py-2.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-display font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center gap-1.5 active:translate-y-px">
                    <span x-text="cart.length > 0 ? 'Lihat Pesanan' : 'Keranjang'"></span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- 5. Mobile / Tablet Portrait: Slide-Up Order Drawer (< lg) -->
        <div x-show="mobileCartOpen" 
             class="lg:hidden fixed inset-0 z-50 flex flex-col justify-end"
             x-cloak>
            <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-[2px] transition-opacity"
                 @click="mobileCartOpen = false"></div>

            <div class="relative bg-white rounded-t-[14px] max-h-[85vh] flex flex-col w-full border-t border-[#E7E0D8] shadow-2xl z-10 animate-in slide-in-from-bottom duration-200"
                 @click.stop>
                <!-- Drawer Header -->
                <div class="pt-2.5 pb-2 px-4 border-b border-[#E7E0D8] bg-[#FAF6F0]/60 shrink-0">
                    <div class="w-10 h-1 bg-stone-300 rounded-full mx-auto mb-2"></div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Pesanan Saat Ini</h2>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-stone-200 text-stone-700 font-mono" x-text="`${cart.length} item`"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="holdCurrentOrder()" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-amber-800 font-bold px-2 py-0.5 rounded-[4px] bg-amber-100 transition">
                                Hold [F4]
                            </button>
                            <button type="button" 
                                    @click="mobileCartOpen = false" 
                                    class="p-1 rounded-[6px] text-stone-400 hover:text-stone-700 text-sm font-bold">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Order Type Selector -->
                    <div class="grid grid-cols-5 gap-1 text-[10px] font-semibold mt-2">
                        <button type="button"
                                @click="setOrderType('DINE_IN')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'DINE_IN' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Dine In
                        </button>
                        <button type="button"
                                @click="setOrderType('TAKE_AWAY')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Bungkus
                        </button>
                        <button type="button"
                                @click="setOrderType('GOFOOD')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-700 text-white border-emerald-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            GoFood
                        </button>
                        <button type="button"
                                @click="setOrderType('GRABFOOD')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-700 text-white border-green-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Grab
                        </button>
                        <button type="button"
                                @click="setOrderType('SHOPEEFOOD')"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-700 text-white border-orange-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Shopee
                        </button>
                    </div>

                    <!-- Dine-In Table Picker -->
                    <template x-if="orderType === 'DINE_IN'">
                        <div class="pt-2 border-t border-stone-200 mt-2">
                            <span class="text-[10px] font-bold text-stone-500 uppercase block mb-1">Pilih Meja:</span>
                            <div class="flex gap-1 overflow-x-auto pb-1">
                                <template x-for="tbl in tables" :key="tbl.id">
                                    <button type="button"
                                            @click="selectTable(tbl)"
                                            class="py-1 px-2 rounded-[4px] text-[10px] font-bold whitespace-nowrap border"
                                            :class="selectedTableId === tbl.id ? 'bg-[#EA580C] text-white border-[#EA580C]' : 'bg-white text-stone-700 border-stone-200'"
                                            x-text="tbl.name">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2 min-h-0 bg-white">
                    <template x-for="(item, idx) in cart" :key="item.product_id">
                        <div class="bg-white rounded-[6px] p-2.5 border border-[#E7E0D8] space-y-1.5">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-stone-900 truncate" x-text="item.name"></h4>
                                    <span class="text-[10px] font-mono tabular-nums text-stone-500">
                                        @Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-stone-900 font-mono tabular-nums">
                                    Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <div class="pt-1 border-t border-stone-100 flex items-center justify-between gap-2">
                                <input type="text" 
                                       x-model="item.notes" 
                                       placeholder="Catatan menu..." 
                                       class="flex-1 text-[10px] bg-stone-50 border border-stone-200 rounded-[4px] px-2 py-0.5 placeholder:text-stone-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#EA580C]">

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" 
                                            @click="updateQuantity(idx, -1)" 
                                            class="w-7 h-7 rounded-[4px] bg-stone-100 text-stone-700 flex items-center justify-center font-bold text-xs">
                                        -
                                    </button>
                                    <span class="text-xs font-bold text-stone-900 w-5 text-center font-mono tabular-nums" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQuantity(idx, 1)" 
                                            class="w-7 h-7 rounded-[4px] bg-stone-100 text-stone-700 flex items-center justify-center font-bold text-xs">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Drawer Totals & Bayar Button -->
                <div class="p-3 border-t border-[#E7E0D8] bg-[#FAF6F0] space-y-1.5 shrink-0">
                    <div class="p-2.5 rounded-[6px] bg-[#5B0A14] text-white flex justify-between items-center">
                        <div>
                            <span class="text-[9px] text-[#FFB800] uppercase tracking-wider font-bold block">Total Tagihan</span>
                            <span class="text-base font-display font-black text-white font-mono tabular-nums leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-white/10 text-stone-200" x-text="orderType"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5">
                        <button type="button"
                                @click="printKitchenTicket()"
                                class="h-10 rounded-[6px] bg-white border border-[#E7E0D8] font-bold text-xs text-stone-700">
                            Tiket Dapur (KOT)
                        </button>
                        <button type="button"
                                @click="mobileCartOpen = false; openPaymentModal()"
                                class="h-10 rounded-[6px] bg-[#EA580C] text-white font-bold text-xs">
                            Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Payment Modal (Tunai, QRIS, Transfer, Multi-Payment Split Bill) -->
        <div x-show="paymentModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-[10px] max-w-sm sm:max-w-md w-full border border-[#E7E0D8] shadow-modal my-auto max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in duration-150" 
                 @click.outside="paymentModalOpen = false">
                <!-- Header -->
                <div class="p-3.5 sm:p-4 border-b border-stone-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-display font-bold text-stone-900">Pembayaran Kasir</h3>
                        <span class="text-[11px] text-stone-500">Tipe: <strong class="text-[#5B0A14]" x-text="orderType"></strong> <span x-show="selectedTableName" x-text="`• ${selectedTableName}`"></span></span>
                    </div>
                    <button type="button" @click="paymentModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-3.5 sm:p-4 overflow-y-auto flex-1 min-h-0 space-y-3">
                    <!-- Total Due Box -->
                    <div class="text-center bg-stone-50 p-3 rounded-[6px] border border-stone-200">
                        <span class="text-[10px] text-stone-500 font-bold uppercase tracking-wider block">Total Tagihan</span>
                        <div class="text-2xl font-display font-black text-[#5B0A14] font-mono tabular-nums mt-0.5">
                            Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- Payment Method Tabs (Tunai, QRIS, Transfer, Multi-Payment) -->
                    <div class="grid grid-cols-4 gap-1 text-[11px] font-semibold">
                        <button type="button"
                                @click="selectedPaymentMethod = 'CASH'"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'CASH' ? 'border-[#EA580C] bg-orange-50 text-[#C2410C] font-bold' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            Tunai
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'QRIS'; generateQris();"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'QRIS' ? 'border-[#121212] bg-[#FACC15] text-[#121212] font-black shadow-[2px_2px_0px_#121212]' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            QRIS
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'TRANSFER'"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'TRANSFER' ? 'border-[#EA580C] bg-orange-50 text-[#C2410C] font-bold' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            Transfer
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'MULTI'"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'MULTI' ? 'border-[#EA580C] bg-orange-50 text-[#C2410C] font-bold' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            Kombinasi
                        </button>
                    </div>

                    <!-- Mode CASH: Input Uang Diterima dengan Titik Ribuan -->
                    <div x-show="selectedPaymentMethod === 'CASH'" class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-semibold text-stone-700">Nominal Tunai Diterima</label>
                            <span class="text-[11px] text-stone-400">Pas: <strong>Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span></strong></span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-stone-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedCashGiven"
                                   @input="setFormattedCashGiven($event.target.value)"
                                   placeholder="0"
                                   class="w-full bg-white border border-stone-300 rounded-[6px] pl-9 pr-3 py-1.5 text-sm font-bold text-stone-900 font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                        
                        <!-- Quick Cash Buttons -->
                        <div class="grid grid-cols-4 gap-1.5 text-xs">
                            <button type="button" @click="cashGiven = finalTotal" class="py-1.5 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold border border-stone-200 transition">Uang Pas</button>
                            <button type="button" @click="cashGiven = 20000" class="py-1.5 rounded-[6px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition">20.000</button>
                            <button type="button" @click="cashGiven = 50000" class="py-1.5 rounded-[6px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition">50.000</button>
                            <button type="button" @click="cashGiven = 100000" class="py-1.5 rounded-[6px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition">100.000</button>
                        </div>

                        <!-- Kembalian Tunai -->
                        <div class="flex justify-between items-center text-xs text-emerald-800 bg-emerald-50 p-2.5 rounded-[6px] border border-emerald-200 font-semibold">
                            <span>Kembalian:</span>
                            <span class="font-bold text-emerald-700 font-mono tabular-nums text-sm">
                                Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Mode QRIS: Tampilan QR Code Dinamis Otomatis Nominal -->
                    <div x-show="selectedPaymentMethod === 'QRIS'" class="space-y-3 bg-[#F6F4ED] p-3.5 border-2 border-[#121212] shadow-[3px_3px_0px_#121212] text-center">
                        <!-- Header QRIS Resmi -->
                        <div class="flex items-center justify-between border-b-2 border-stone-800 pb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="bg-[#DC2626] text-white px-1.5 py-0.5 text-[9px] font-black tracking-widest font-mono">QRIS</span>
                                <span class="bg-[#1D4ED8] text-white px-1 py-0.5 text-[8px] font-black font-mono">GPN</span>
                            </div>
                            <span class="text-[9px] font-black font-mono uppercase text-emerald-800 bg-emerald-100 border border-emerald-600 px-1.5 py-0.5">
                                🟢 NOMINAL TERKUNCI OTOMATIS
                            </span>
                        </div>

                        <!-- Merchant & Amount Info -->
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-stone-600 font-mono block">
                                {{ $outlet->qris_merchant_name ?? $outlet->name ?? 'SOTO LAMONGAN LESTARI' }}
                            </span>
                            <div class="text-2xl sm:text-3xl font-display font-black text-[#121212] font-mono tracking-tight my-1">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </div>
                            <span class="text-[9px] text-stone-600 font-mono block">
                                Nominal tagihan telah otomatis terisi. Pembeli tinggal scan dari layar tablet ini!
                            </span>
                        </div>

                        <!-- QR Code Canvas Display -->
                        <div class="p-2.5 bg-white border-2 border-[#121212] shadow-[2px_2px_0px_#121212] inline-block mx-auto relative">
                            <canvas id="qris-pos-canvas" class="mx-auto block"></canvas>
                            
                            <!-- Timer & Refresh -->
                            <div class="mt-2 pt-1 border-t border-stone-200 flex items-center justify-between text-[10px] font-mono text-stone-500">
                                <span>Berlaku: <strong class="text-red-600" x-text="qrisTimerFormatted"></strong></span>
                                <button type="button" @click="generateQris()" class="text-[#1D4ED8] font-bold hover:underline">
                                    🔄 Refresh QR
                                </button>
                            </div>
                        </div>

                        <!-- Action Bar Khusus Tablet Kasir: Sodorkan Layar Penuh -->
                        <div class="flex items-center justify-center gap-2 pt-1">
                            <button type="button" 
                                    @click="openQrisFullScreen()" 
                                    class="px-3.5 py-1.5 bg-[#FACC15] hover:bg-yellow-400 text-[#121212] font-display font-black text-xs uppercase border-2 border-[#121212] shadow-[2px_2px_0px_#121212] active:translate-x-0.5 active:translate-y-0.5 transition-all flex items-center gap-1.5">
                                <span>📱 Perbesar Layar (Sodorkan Tablet ke Pembeli)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mode MULTI: Kombinasi Pembayaran Tunai + Non Tunai (Split Bill) -->
                    <div x-show="selectedPaymentMethod === 'MULTI'" class="space-y-2.5 bg-orange-50/50 p-3 rounded-[8px] border border-orange-200">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-600 block">Kombinasi Pembayaran (Split Bill)</span>
                        
                        <!-- Porsi Tunai -->
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-700 mb-1">Nominal Tunai</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1.5 text-stone-400 font-mono text-xs font-bold">Rp</span>
                                <input type="text" 
                                       inputmode="numeric"
                                       :value="formattedMultiCash"
                                       @input="setFormattedMultiCash($event.target.value)"
                                       placeholder="0"
                                       class="w-full bg-white border border-stone-300 rounded-[6px] pl-9 pr-3 py-1 text-xs font-bold font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                            </div>
                        </div>

                        <!-- Porsi Non-Tunai -->
                        <div class="space-y-1">
                            <div class="flex justify-between items-center">
                                <label class="block text-[11px] font-semibold text-stone-700">Nominal Non-Tunai</label>
                                <div class="flex gap-2 text-[10px] font-bold">
                                    <label class="cursor-pointer flex items-center gap-1">
                                        <input type="radio" value="QRIS" x-model="multiNonCashMethod" class="text-[#EA580C]"> QRIS
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-1">
                                        <input type="radio" value="TRANSFER" x-model="multiNonCashMethod" class="text-[#EA580C]"> Transfer
                                    </label>
                                </div>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1.5 text-stone-400 font-mono text-xs font-bold">Rp</span>
                                <input type="text" 
                                       inputmode="numeric"
                                       :value="formattedMultiNonCash"
                                       @input="setFormattedMultiNonCash($event.target.value)"
                                       placeholder="0"
                                       class="w-full bg-white border border-stone-300 rounded-[6px] pl-9 pr-3 py-1 text-xs font-bold font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                            </div>
                            <template x-if="multiNonCashMethod === 'QRIS' && multiNonCashAmount > 0">
                                <button type="button" 
                                        @click="generateQris()"
                                        class="mt-1 text-[10px] text-[#1D4ED8] font-bold hover:underline block font-mono">
                                    📱 Tampilkan QRIS Non-Tunai (Rp<span x-text="Number(multiNonCashAmount).toLocaleString('id-ID')"></span>)
                                </button>
                            </template>
                        </div>

                        <!-- Ringkasan Split -->
                        <div class="pt-2 border-t border-orange-200/60 text-xs space-y-1">
                            <div class="flex justify-between text-stone-600">
                                <span>Total Terkumpul:</span>
                                <span class="font-bold font-mono tabular-nums">Rp<span x-text="Number(totalMultiPaid).toLocaleString('id-ID')"></span></span>
                            </div>
                            <template x-if="remainingMultiDue > 0">
                                <div class="flex justify-between text-red-600 font-bold">
                                    <span>Kurang Bayar:</span>
                                    <span class="font-mono tabular-nums">Rp<span x-text="Number(remainingMultiDue).toLocaleString('id-ID')"></span></span>
                                </div>
                            </template>
                            <template x-if="changeDue > 0">
                                <div class="flex justify-between text-emerald-700 font-bold">
                                    <span>Kembalian Tunai:</span>
                                    <span class="font-mono tabular-nums">Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Nama / Catatan Pelanggan</label>
                        <input type="text" x-model="customerName" placeholder="Contoh: Bpk Ahmad / Meja 04" class="w-full bg-white border border-stone-200 rounded-[6px] px-3 py-1.5 text-xs placeholder:text-stone-400 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>
                </div>

                <!-- Confirm Action Button -->
                <div class="p-3.5 sm:p-4 border-t border-stone-100 bg-stone-50 shrink-0">
                    <button type="button"
                            @click="processCheckout()"
                            :disabled="isProcessingCheckout || (selectedPaymentMethod === 'CASH' && cashGiven < finalTotal) || (selectedPaymentMethod === 'MULTI' && totalMultiPaid < finalTotal)"
                            class="w-full h-11 rounded-[6px] disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center justify-center gap-1.5 active:translate-y-px"
                            :class="selectedPaymentMethod === 'QRIS' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-[#EA580C] hover:bg-[#D94E07]'">
                        <span x-text="isProcessingCheckout ? 'Menyimpan Transaksi...' : (selectedPaymentMethod === 'QRIS' ? '✓ Selesaikan Pembayaran QRIS (Lunas)' : 'Selesaikan Pembayaran')"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Fullscreen QRIS Modal khusus Tablet Kasir (Sodorkan ke Pembeli) -->
        <div x-show="qrisFullScreenModalOpen" 
             class="fixed inset-0 bg-[#121212]/90 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 animate-in fade-in"
             x-cloak>
            <div class="bg-white border-4 border-[#121212] shadow-[8px_8px_0px_#FACC15] max-w-sm sm:max-w-md w-full p-5 sm:p-6 text-center space-y-4 my-auto"
                 @click.outside="qrisFullScreenModalOpen = false">
                
                <!-- Top Banner QRIS -->
                <div class="flex items-center justify-between border-b-2 border-[#121212] pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="bg-[#DC2626] text-white px-2 py-0.5 text-xs font-black tracking-widest font-mono">QRIS</span>
                        <span class="bg-[#1D4ED8] text-white px-1.5 py-0.5 text-[10px] font-black font-mono">GPN</span>
                    </div>
                    <button type="button" 
                            @click="qrisFullScreenModalOpen = false" 
                            class="text-stone-700 hover:text-red-600 font-black text-sm p-1">
                        ✕ Tutup (ESC)
                    </button>
                </div>

                <!-- Outlet Name & Big Amount -->
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-stone-600 font-mono block">
                        {{ $outlet->qris_merchant_name ?? $outlet->name ?? 'SOTO LAMONGAN LESTARI' }}
                    </span>
                    <div class="text-3xl sm:text-4xl font-display font-black text-[#121212] font-mono tracking-tight my-1">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </div>
                    <span class="inline-block bg-emerald-100 text-emerald-800 border border-emerald-500 font-mono text-[10px] font-bold px-2 py-0.5">
                        🟢 NOMINAL OTOMATIS TERKUNCI
                    </span>
                </div>

                <!-- Big Canvas QRIS -->
                <div class="p-3 bg-white border-2 border-[#121212] shadow-[4px_4px_0px_#121212] inline-block mx-auto">
                    <canvas id="qris-fullscreen-canvas" class="mx-auto block"></canvas>
                </div>

                <p class="text-xs font-mono text-stone-600 leading-snug">
                    Silakan scan menggunakan <strong>BCA, Livin Mandiri, BRImo, GoPay, ShopeePay, Dana, atau OVO</strong>.<br>
                    Nominal tagihan sudah terkunci otomatis.
                </p>

                <!-- Bottom Action -->
                <div class="pt-2 flex flex-col gap-2">
                    <button type="button" 
                            @click="qrisFullScreenModalOpen = false; processCheckout();"
                            :disabled="isProcessingCheckout"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-display font-black text-sm uppercase tracking-wider border-2 border-[#121212] shadow-[4px_4px_0px_#121212] active:translate-x-0.5 active:translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="isProcessingCheckout ? 'Menyimpan...' : 'Pelanggan Sudah Bayar (Lunas)'"></span>
                    </button>

                    <button type="button" 
                            @click="qrisFullScreenModalOpen = false"
                            class="w-full py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-mono text-xs font-bold border border-stone-300">
                        Kembali ke Kasir
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. Success Modal -->
        <div x-show="successModalOpen"
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-[10px] max-w-sm sm:max-w-md w-full border border-[#E7E0D8] shadow-modal my-auto max-h-[92vh] flex flex-col overflow-hidden p-4 sm:p-5 text-center animate-in fade-in duration-150"
                 @click.outside="successModalOpen = false">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-[6px] flex items-center justify-center mx-auto mb-2 font-bold shrink-0">
                    ✓
                </div>

                <h3 class="text-sm font-display font-bold text-stone-900">Pembayaran Berhasil</h3>
                <p class="text-[11px] text-stone-500 mt-0.5">Transaksi lunas, stok bahan baku resep otomatis dipotong.</p>

                <!-- Receipt Summary -->
                <div class="bg-stone-50 rounded-[6px] p-3 my-3 text-left border border-stone-200 text-xs space-y-1.5 overflow-y-auto max-h-48">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>No. Struk</span>
                        <span class="font-mono tabular-nums font-bold text-stone-900" x-text="completedOrder?.order_number || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>Metode</span>
                        <span class="font-semibold text-stone-900" x-text="completedOrder?.payment?.method || (completedOrder?.payments ? 'KOMBINASI' : 'CASH')"></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500 pt-1 border-t border-stone-200">
                        <span class="font-bold text-stone-900">Total</span>
                        <span class="font-black text-stone-900 font-mono tabular-nums">Rp<span x-text="Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <template x-if="completedOrder?.changeDue > 0">
                        <div class="flex justify-between items-center text-emerald-700 font-bold">
                            <span>Kembalian</span>
                            <span class="font-mono tabular-nums">Rp<span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span></span>
                        </div>
                    </template>
                </div>

                <div class="space-y-1.5 shrink-0">
                    <button type="button"
                            @click="window.thermalPrinter && window.thermalPrinter.printReceipt(completedOrder)"
                            class="w-full h-10 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs transition flex items-center justify-center gap-1.5 active:translate-y-px">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Struk Thermal Pelanggan (58mm)</span>
                    </button>
                    
                    <button type="button"
                            @click="printKitchenTicket()"
                            class="w-full h-8 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition flex items-center justify-center">
                        Cetak Tiket Dapur (KOT)
                    </button>

                    <button type="button"
                            @click="successModalOpen = false"
                            class="w-full h-8 rounded-[6px] text-stone-500 hover:text-stone-800 font-semibold text-xs transition flex items-center justify-center">
                        Transaksi Baru (Selesai)
                    </button>
                </div>
            </div>
        </div>

        <!-- 8. Open Bills Modal (Daftar Tagihan Gantung / Hold Orders) -->
        <div x-show="openBillsModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-[10px] max-w-lg w-full border border-[#E7E0D8] shadow-modal my-auto max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in duration-150"
                 @click.outside="openBillsModalOpen = false">
                <div class="p-3.5 sm:p-4 border-b border-stone-100 flex items-center justify-between shrink-0 bg-[#FAF6F0]/60">
                    <div>
                        <h3 class="text-sm font-display font-bold text-stone-900">Daftar Open Bill Aktif</h3>
                        <p class="text-[11px] text-stone-500">Pesanan pelanggan makan di tempat yang belum dilunasi.</p>
                    </div>
                    <button type="button" @click="openBillsModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <div class="p-3.5 sm:p-4 overflow-y-auto flex-1 space-y-2">
                    <template x-if="openBills.length === 0">
                        <div class="p-6 text-center text-stone-400 text-xs">
                            Tidak ada Open Bill yang sedang aktif.
                        </div>
                    </template>

                    <template x-for="bill in openBills" :key="bill.id">
                        <div class="p-3 rounded-[6px] border border-[#E7E0D8] hover:border-amber-400 bg-white space-y-1.5 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold text-stone-900 block" x-text="bill.customer_name || 'Pelanggan'"></span>
                                    <span class="text-[10px] font-mono text-stone-400" x-text="`No: ${bill.order_number}`"></span>
                                </div>
                                <span class="text-xs font-bold text-[#C2410C] font-mono tabular-nums">
                                    Rp<span x-text="Number(bill.final_amount).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <div class="text-[11px] text-stone-600 line-clamp-1">
                                <span x-text="`${bill.items?.length || 0} menu:`"></span>
                                <template x-for="itm in (bill.items || [])" :key="itm.id">
                                    <span class="text-stone-500 mr-1" x-text="`${itm.name || itm.product?.name} (${itm.quantity})`"></span>
                                </template>
                            </div>

                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between gap-2">
                                <span class="text-[10px] text-stone-400 font-mono" x-text="new Date(bill.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
                                <button type="button" 
                                        @click="resumeOpenBill(bill)"
                                        class="px-3 py-1 rounded-[4px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">
                                    Panggil Tagihan & Lunasi
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 9. Buka Shift Kasir Modal -->
        <div x-show="startShiftModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-4 sm:p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="startShiftModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Buka Shift Kasir Baru</h3>
                    <button type="button" @click="startShiftModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Modal Awal Laci Kasir (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-stone-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedStartCash"
                                   @input="setFormattedStartCash($event.target.value)"
                                   placeholder="Contoh: 200.000" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] pl-9 pr-3 py-1.5 text-xs font-bold font-mono tabular-nums text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan Kasir (Opsional)</label>
                        <input type="text" x-model="startCashNotes" placeholder="Contoh: Shift Pagi Kasir 1" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="startShiftModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="button" @click="startShift()" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Buka Shift Sekarang</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10. Kas Keluar / Petty Cash Modal -->
        <div x-show="pettyCashModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-4 sm:p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="pettyCashModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Pencatatan Kas Keluar (Petty Cash)</h3>
                    <button type="button" @click="pettyCashModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nominal Kas Keluar (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-stone-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedPettyCash"
                                   @input="setFormattedPettyCash($event.target.value)"
                                   placeholder="Contoh: 25.000" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] pl-9 pr-3 py-1.5 text-xs font-bold font-mono tabular-nums text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Keterangan Pengeluaran <span class="text-red-500">*</span></label>
                        <input type="text" x-model="pettyCashReason" placeholder="Contoh: Beli Es Batu kristal, Gas Elpiji 3kg" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="pettyCashModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="button" @click="recordPettyCash()" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">Catat Kas Keluar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 11. Tutup Shift Kasir (Blind Close Modal) -->
        <div x-show="closeShiftModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-4 sm:p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="closeShiftModalOpen = false">
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Tutup Shift Kasir (Blind Close)</h3>
                        <p class="text-[10px] text-stone-500">Hitung uang fisik di laci sebelum sistem menampilkan selisih.</p>
                    </div>
                    <button type="button" @click="closeShiftModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Uang Fisik Kasir di Laci (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-stone-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedActualCash"
                                   @input="setFormattedActualCash($event.target.value)"
                                   placeholder="Contoh: 1.450.000" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] pl-9 pr-3 py-1.5 text-xs font-bold font-mono tabular-nums text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan Tutup Shift (Opsional)</label>
                        <input type="text" x-model="closeShiftNotes" placeholder="Contoh: Selesai shift sore kondisi aman" class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="closeShiftModalOpen = false" class="px-3 py-1.5 rounded-[6px] bg-stone-100 text-stone-600 text-xs font-medium hover:bg-stone-200 transition">Batal</button>
                        <button type="button" @click="closeShift()" class="px-3.5 py-1.5 rounded-[6px] bg-[#5B0A14] hover:bg-[#7A0F1C] text-white text-xs font-bold transition">Hitung & Tutup Shift</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 12. Ringkasan Tutup Shift (Shift Summary Report Modal) -->
        <div x-show="shiftSummaryModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-4 sm:p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150 text-center"
                 @click.outside="shiftSummaryModalOpen = false">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto mb-2 font-bold text-sm">
                    ✓
                </div>
                <h3 class="text-sm font-display font-bold text-stone-900">Shift Kasir Ditutup</h3>
                <p class="text-[11px] text-stone-500">Laporan audit selisih kas telah disimpan di pembukuan.</p>

                <div class="bg-stone-50 rounded-[6px] p-3 my-3 text-left border border-stone-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-stone-500">Modal Awal:</span>
                        <span class="font-mono tabular-nums font-semibold">Rp<span x-text="Number(shiftReport?.start_cash || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Penjualan Tunai:</span>
                        <span class="font-mono tabular-nums font-semibold text-emerald-700">+Rp<span x-text="Number(shiftReport?.cash_sales || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Kas Keluar (Petty Cash):</span>
                        <span class="font-mono tabular-nums font-semibold text-red-600">-Rp<span x-text="Number(shiftReport?.petty_cash_out || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between border-t border-stone-200 pt-1 font-bold">
                        <span>Ekspektasi Kas Laci:</span>
                        <span class="font-mono tabular-nums">Rp<span x-text="Number(shiftReport?.expected_cash || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Uang Fisik Dihitung:</span>
                        <span class="font-mono tabular-nums font-bold">Rp<span x-text="Number(shiftReport?.actual_cash || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between border-t border-dotted border-stone-300 pt-1 font-bold"
                         :class="(shiftReport?.difference || 0) < 0 ? 'text-red-600' : ((shiftReport?.difference || 0) > 0 ? 'text-emerald-700' : 'text-stone-900')">
                        <span>Selisih (Short/Over):</span>
                        <span class="font-mono tabular-nums">
                            <span x-text="(shiftReport?.difference || 0) > 0 ? '+Rp' : 'Rp'"></span>
                            <span x-text="Number(shiftReport?.difference || 0).toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                <button type="button" @click="shiftSummaryModalOpen = false" class="w-full h-9 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white text-xs font-bold transition">
                    Tutup Laporan
                </button>
            </div>
        </div>

        <!-- 13. 58mm/80mm Thermal Receipt Container (Cetak Struk Transaksi Pelanggan) -->
        <div id="printable-receipt" class="hidden text-black font-mono text-[11px] leading-tight select-none">
            <div class="text-center pb-2 border-b border-dashed border-black">
                <h2 class="text-xs font-bold tracking-wider uppercase">SOTO LAMONGAN LESTARI</h2>
                <p class="text-[10px] mt-0.5">{{ $outlet->name ?? 'Cabang Utama' }}</p>
                <p class="text-[10px]">Telp: 0852-1325-4219</p>
            </div>

            <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>No: <strong x-text="completedOrder?.order_number"></strong></span>
                    <span x-text="new Date(completedOrder?.created_at || Date.now()).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
                </div>
                <div class="flex justify-between">
                    <span>Tgl: <span x-text="new Date(completedOrder?.created_at || Date.now()).toLocaleDateString('id-ID')"></span></span>
                    <span class="font-bold uppercase" x-text="orderType"></span>
                </div>
                <div class="flex justify-between" x-show="completedOrder?.customer_name">
                    <span>Plg: <span x-text="completedOrder?.customer_name"></span></span>
                </div>
            </div>

            <!-- Items -->
            <div class="py-1.5 border-b border-dashed border-black space-y-1">
                <template x-for="item in (completedOrder?.items || [])" :key="item.name">
                    <div>
                        <div class="font-bold text-[11px]" x-text="item.name"></div>
                        <div class="flex justify-between text-[10px]">
                            <span x-text="`${item.quantity} x ${Number(item.unit_price).toLocaleString('id-ID')}`"></span>
                            <span class="font-bold" x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                        </div>
                        <template x-if="item.notes">
                            <div class="text-[9px] italic" x-text="`* ${item.notes}`"></div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Totals -->
            <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span x-text="Number(completedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between" x-show="completedOrder?.discount_amount > 0">
                    <span>Diskon</span>
                    <span x-text="`-${Number(completedOrder?.discount_amount || 0).toLocaleString('id-ID')}`"></span>
                </div>
                <div class="flex justify-between">
                    <span>PPN (11%)</span>
                    <span x-text="Number(completedOrder?.tax_amount || 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-black">
                    <span>TOTAL</span>
                    <span x-text="`Rp${Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')}`"></span>
                </div>
            </div>

            <!-- Payment info -->
            <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>Metode</span>
                    <span class="font-bold uppercase" x-text="completedOrder?.payment?.method || (completedOrder?.payments ? 'KOMBINASI' : 'CASH')"></span>
                </div>
                <template x-if="completedOrder?.changeDue > 0">
                    <div class="flex justify-between font-bold">
                        <span>Kembalian</span>
                        <span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="text-center pt-2 text-[10px] space-y-0.5">
                <p class="font-bold">Rasa Mantap, Harga Bersahabat</p>
                <p>Terima Kasih Atas Kunjungan Anda!</p>
                <p class="text-[8px] text-gray-500 pt-1">SLL Smart POS Cloud</p>
            </div>
        </div>

        <!-- 14. Kitchen Order Ticket (KOT) Thermal Container (Khusus Dapur & Bar, Tanpa Harga) -->
        <div id="printable-kot" class="hidden text-black font-mono text-[11px] leading-tight select-none">
            <div class="text-center pb-2 border-b-2 border-black">
                <h2 class="text-sm font-black tracking-wider uppercase">TIKET DAPUR (KOT)</h2>
                <p class="text-xs font-bold">{{ $outlet->name ?? 'Cabang Utama' }}</p>
            </div>

            <div class="py-2 border-b border-black text-xs space-y-0.5">
                <div class="flex justify-between font-bold">
                    <span>TIPE:</span>
                    <span class="uppercase text-sm" x-text="orderType"></span>
                </div>
                <div class="flex justify-between">
                    <span>MEJA / PLG:</span>
                    <span class="font-black text-sm" x-text="selectedTableName || customerName || 'Dine-In'"></span>
                </div>
                <div class="flex justify-between text-[10px]">
                    <span>WAKTU:</span>
                    <span x-text="new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
                </div>
            </div>

            <!-- List Menu yang Dimasak (Tulisan Jelas & Catatan Spesial) -->
            <div class="py-2 border-b-2 border-black space-y-2">
                <template x-for="item in (cart.length > 0 ? cart : (completedOrder?.items || []))" :key="item.name">
                    <div class="pb-1 border-b border-dotted border-stone-400">
                        <div class="flex justify-between items-start text-xs font-black">
                            <span x-text="item.name"></span>
                            <span class="text-sm px-1.5 py-0.2 bg-black text-white rounded font-mono" x-text="`${item.quantity}x`"></span>
                        </div>
                        <template x-if="item.notes">
                            <div class="text-[11px] font-bold text-black mt-0.5 uppercase tracking-wide">
                                ➔ CATATAN: <span x-text="item.notes"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="text-center pt-2 text-[10px] font-bold">
                *** SEGERA DISAJIKAN PANAS ***
            </div>
        </div>
    </div>
</body>
</html>