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
            userId: "{{ auth()->id() ?? '1' }}",
            shiftId: "{{ $shift->id ?? '' }}"
        };
        window.POS_INITIAL_CATEGORIES = @json($categories ?? []);
        window.POS_INITIAL_PRODUCTS = @json($products ?? []);
    </script>
</head>
<body class="h-full antialiased font-sans bg-[#FAF6F0] text-[#292524] select-none overflow-hidden"
      x-data="posTerminal()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col overflow-hidden">
        <!-- 1. Top Bar: Narrow SLL Brand Rail (Responsive) -->
        <header class="h-12 sm:h-13 bg-[#5B0A14] border-b border-[#4A0710] px-3 sm:px-4 flex items-center justify-between shrink-0 text-white z-30">
            <!-- Brand & Outlet Info -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[6px] bg-[#EA580C] text-white flex items-center justify-center font-display font-black text-xs shrink-0 tracking-wider">
                    SLL
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="font-display font-bold text-xs tracking-wide text-white leading-tight truncate">SOTO LAMONGAN LESTARI</span>
                        <span class="hidden sm:inline-block text-[9px] font-bold px-1.5 py-0.5 rounded-[4px] bg-[#FFB800] text-[#5B0A14] uppercase tracking-wider shrink-0">KASIR</span>
                    </div>
                    <div class="text-[10px] sm:text-[11px] text-stone-300 font-medium truncate">
                        <span>{{ $outlet->name ?? 'Cabang Utama' }}</span>
                        <span class="text-stone-400 mx-1">•</span>
                        <span class="text-stone-300">Shift Pagi</span>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <!-- Status Koneksi -->
                <div class="flex items-center gap-1.5 px-2 sm:px-2.5 py-1 rounded-[6px] text-xs font-semibold"
                     :class="isOnline ? 'bg-emerald-950 border border-emerald-800 text-emerald-400' : 'bg-amber-950 border border-amber-800 text-amber-400'"
                     :title="isOnline ? 'Terhubung ke server cloud' : 'Mode offline lokal'">
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
                    <span class="hidden md:inline">Printer Thermal</span>
                </button>

                <!-- Backoffice Link -->
                <a href="{{ route('dashboard') }}" class="px-2.5 sm:px-3 py-1.5 text-xs font-bold text-white bg-[#EA580C] hover:bg-[#D94E07] rounded-[6px] transition flex items-center gap-1 active:translate-y-px">
                    <svg class="w-3.5 h-3.5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="hidden sm:inline">Backoffice</span>
                </a>
            </div>
        </header>

        <!-- 2. Workspace: Catalog Center + Pinned Order Panel Right (>= lg) -->
        <main class="flex-1 flex overflow-hidden relative">
            <!-- Center: Product Catalog -->
            <div class="flex-1 w-full flex flex-col p-2.5 sm:p-4 overflow-hidden bg-[#FAF6F0]">
                
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

                <!-- Product Tiles Grid: Text-Only Minimalist POS Tiles (Fast, Clear, High Density) -->
                <div class="flex-1 overflow-y-auto min-h-0 content-start auto-rows-max grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-2.5 p-0.5 pb-24 lg:pb-8">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="bg-white border border-[#E7E0D8] hover:border-[#EA580C] hover:bg-[#FFFDF9] rounded-[8px] p-2.5 sm:p-3 flex flex-col justify-between cursor-pointer transition active:scale-[0.98] group min-h-[96px] sm:min-h-[104px] relative select-none hover:shadow-subtle">
                            
                            <div>
                                <!-- SKU / Category indicator -->
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[9px] font-mono font-bold uppercase tracking-wider text-stone-400 truncate" x-text="product.sku || 'Menu SLL'"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EA580C]/40 group-hover:bg-[#EA580C] transition shrink-0"></span>
                                </div>

                                <!-- Name (Bold, clear display) -->
                                <h3 class="text-xs sm:text-sm font-bold text-stone-900 group-hover:text-[#5B0A14] line-clamp-2 leading-tight" x-text="product.name"></h3>
                            </div>

                            <!-- Price & Quick Add Button -->
                            <div class="mt-2 pt-1.5 border-t border-stone-100 flex items-center justify-between">
                                <span class="text-xs sm:text-sm font-bold text-[#C2410C] font-mono tabular-nums">
                                    Rp<span x-text="Number(product.selling_price).toLocaleString('id-ID')"></span>
                                </span>
                                <button type="button" 
                                        class="w-6 h-6 sm:w-7 sm:h-7 rounded-[4px] bg-stone-100 group-hover:bg-[#EA580C] text-stone-700 group-hover:text-white flex items-center justify-center text-xs sm:text-sm font-bold transition active:translate-y-px shrink-0"
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
                        <button type="button" 
                                @click="cart = []" 
                                x-show="cart.length > 0"
                                class="text-[11px] text-red-600 hover:text-red-700 font-semibold px-2 py-0.5 rounded-[4px] hover:bg-red-50 transition">
                            Kosongkan
                        </button>
                    </div>

                    <!-- Order Type Selector (Dine in, Take away, GoFood, GrabFood, ShopeeFood) -->
                    <div class="grid grid-cols-5 gap-1 text-[10px] font-semibold">
                        <button type="button"
                                @click="orderType = 'DINE_IN'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'DINE_IN' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Dine In
                        </button>
                        <button type="button"
                                @click="orderType = 'TAKE_AWAY'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Bungkus
                        </button>
                        <button type="button"
                                @click="orderType = 'GOFOOD'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-700 text-white border-emerald-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            GoFood
                        </button>
                        <button type="button"
                                @click="orderType = 'GRABFOOD'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-700 text-white border-green-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Grab
                        </button>
                        <button type="button"
                                @click="orderType = 'SHOPEEFOOD'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-700 text-white border-orange-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Shopee
                        </button>
                    </div>
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
                                       placeholder="Catatan: kuah pisah/koya..." 
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

                <!-- Totals & Dominant Checkout Button -->
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
                            <span class="text-[9px] text-[#FFB800] uppercase tracking-wider font-bold block">Total Tagihan</span>
                            <span class="text-base font-display font-black text-white font-mono tabular-nums leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-white/10 text-stone-200" x-text="orderType"></span>
                    </div>

                    <!-- Dominant Bayar Button (F2 / Tap target >= 48px) -->
                    <button type="button"
                            @click="paymentModalOpen = true; cashGiven = finalTotal"
                            :disabled="cart.length === 0"
                            class="w-full h-12 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center justify-center gap-1.5 active:translate-y-px mt-1.5">
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
            <!-- Dark Backdrop -->
            <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-[2px] transition-opacity"
                 @click="mobileCartOpen = false"></div>

            <!-- Bottom Sheet Content -->
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
                                    @click="cart = []" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-red-600 hover:text-red-700 font-semibold px-2 py-0.5 rounded-[4px] hover:bg-red-50 transition">
                                Kosongkan
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
                                @click="orderType = 'DINE_IN'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'DINE_IN' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Dine In
                        </button>
                        <button type="button"
                                @click="orderType = 'TAKE_AWAY'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-[#5B0A14] text-white border-[#5B0A14] font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Bungkus
                        </button>
                        <button type="button"
                                @click="orderType = 'GOFOOD'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-700 text-white border-emerald-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            GoFood
                        </button>
                        <button type="button"
                                @click="orderType = 'GRABFOOD'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-700 text-white border-green-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Grab
                        </button>
                        <button type="button"
                                @click="orderType = 'SHOPEEFOOD'"
                                class="py-1 rounded-[4px] border text-center transition"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-700 text-white border-orange-700 font-bold' : 'bg-white text-stone-600 border-[#E7E0D8] hover:bg-stone-50'">
                            Shopee
                        </button>
                    </div>
                </div>

                <!-- Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2 min-h-0 bg-white">
                    <template x-if="cart.length === 0">
                        <div class="h-32 flex flex-col items-center justify-center text-center p-4 text-stone-400">
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
                                       placeholder="Catatan: kuah pisah/koya..." 
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

                <!-- Drawer Totals & Dominant Bayar Button -->
                <div class="p-3 border-t border-[#E7E0D8] bg-[#FAF6F0] space-y-1.5 shrink-0">
                    <div class="flex justify-between text-xs text-stone-600">
                        <span>Subtotal</span>
                        <span class="font-mono tabular-nums font-semibold">Rp<span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-stone-600">
                        <span>PPN (11%)</span>
                        <span class="font-mono tabular-nums font-semibold">Rp<span x-text="Number(taxAmount).toLocaleString('id-ID')"></span></span>
                    </div>
                    
                    <div class="p-2.5 rounded-[6px] bg-[#5B0A14] text-white flex justify-between items-center mt-1">
                        <div>
                            <span class="text-[9px] text-[#FFB800] uppercase tracking-wider font-bold block">Total Tagihan</span>
                            <span class="text-base font-display font-black text-white font-mono tabular-nums leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-white/10 text-stone-200" x-text="orderType"></span>
                    </div>

                    <button type="button"
                            @click="mobileCartOpen = false; paymentModalOpen = true; cashGiven = finalTotal"
                            :disabled="cart.length === 0"
                            class="w-full h-12 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center justify-center gap-1.5 active:translate-y-px mt-1.5">
                        <span>Bayar Sekarang (Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 6. Payment Modal (Fully responsive, scrollable inside max-h-[92vh]) -->
        <div x-show="paymentModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-[10px] max-w-sm sm:max-w-md w-full border border-[#E7E0D8] shadow-modal my-auto max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in duration-150" 
                 @click.outside="paymentModalOpen = false">
                <!-- Header -->
                <div class="p-3.5 sm:p-4 border-b border-stone-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-display font-bold text-stone-900">Pembayaran Kasir</h3>
                        <span class="text-[11px] text-stone-500">Tipe: <strong class="text-[#5B0A14]" x-text="orderType"></strong></span>
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

                    <!-- Payment Method Tabs -->
                    <div class="grid grid-cols-3 gap-1.5 text-xs font-semibold">
                        <button type="button"
                                @click="selectedPaymentMethod = 'CASH'"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'CASH' ? 'border-[#EA580C] bg-orange-50 text-[#C2410C] font-bold' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            Tunai
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'QRIS'"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'QRIS' ? 'border-[#EA580C] bg-orange-50 text-[#C2410C] font-bold' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            QRIS
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'TRANSFER'"
                                class="py-2 rounded-[6px] border text-center transition"
                                :class="selectedPaymentMethod === 'TRANSFER' ? 'border-[#EA580C] bg-orange-50 text-[#C2410C] font-bold' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                            Transfer
                        </button>
                    </div>

                    <!-- Cash Numpad / Inputs -->
                    <div x-show="selectedPaymentMethod === 'CASH'" class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-semibold text-stone-700">Nominal Diterima</label>
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
                        
                        <!-- Quick Cash Buttons (6px radius) -->
                        <div class="grid grid-cols-4 gap-1.5 text-xs">
                            <button type="button" @click="cashGiven = finalTotal" class="py-1.5 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold border border-stone-200 transition">Uang Pas</button>
                            <button type="button" @click="cashGiven = 20000" class="py-1.5 rounded-[6px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition">20.000</button>
                            <button type="button" @click="cashGiven = 50000" class="py-1.5 rounded-[6px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition">50.000</button>
                            <button type="button" @click="cashGiven = 100000" class="py-1.5 rounded-[6px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition">100.000</button>
                        </div>

                        <!-- Automatic Change Calculation -->
                        <div class="flex justify-between items-center text-xs text-emerald-800 bg-emerald-50 p-2.5 rounded-[6px] border border-emerald-200 font-semibold">
                            <span>Kembalian:</span>
                            <span class="font-bold text-emerald-700 font-mono tabular-nums text-sm">
                                Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Nama / Meja Pelanggan (Opsional)</label>
                        <input type="text" x-model="customerName" placeholder="Contoh: Meja 4 / Bpk Joko" class="w-full bg-white border border-stone-200 rounded-[6px] px-3 py-1.5 text-xs placeholder:text-stone-400 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>
                </div>

                <!-- Confirm Action Button (Sticky Footer) -->
                <div class="p-3.5 sm:p-4 border-t border-stone-100 bg-stone-50 shrink-0">
                    <button type="button"
                            @click="processCheckout()"
                            :disabled="isProcessingCheckout || (selectedPaymentMethod === 'CASH' && cashGiven < finalTotal)"
                            class="w-full h-11 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center justify-center gap-1.5 active:translate-y-px">
                        <span x-text="isProcessingCheckout ? 'Menyimpan...' : 'Selesaikan Pembayaran'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. Success Modal (Responsive, scrollable inside max-h-[92vh]) -->
        <div x-show="successModalOpen"
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-[10px] max-w-sm sm:max-w-md w-full border border-[#E7E0D8] shadow-modal my-auto max-h-[92vh] flex flex-col overflow-hidden p-4 sm:p-5 text-center animate-in fade-in duration-150"
                 @click.outside="successModalOpen = false">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-[6px] flex items-center justify-center mx-auto mb-2 font-bold shrink-0">
                    ✓
                </div>

                <h3 class="text-sm font-display font-bold text-stone-900">Pembayaran Berhasil</h3>
                <p class="text-[11px] text-stone-500 mt-0.5">Transaksi lunas dan otomatis tersimpan di pembukuan.</p>

                <!-- Receipt Summary -->
                <div class="bg-stone-50 rounded-[6px] p-3 my-3 text-left border border-stone-200 text-xs space-y-1.5 overflow-y-auto max-h-48">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>No. Struk</span>
                        <span class="font-mono tabular-nums font-bold text-stone-900" x-text="completedOrder?.order_number || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>Metode</span>
                        <span class="font-semibold text-stone-900" x-text="completedOrder?.payment?.method || 'CASH'"></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500 pt-1 border-t border-stone-200">
                        <span class="font-bold text-stone-900">Total</span>
                        <span class="font-black text-stone-900 font-mono tabular-nums">Rp<span x-text="Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <template x-if="completedOrder?.payment?.method === 'CASH'">
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
                        <span>Cetak Struk Thermal (58mm)</span>
                    </button>
                    
                    <button type="button"
                            @click="successModalOpen = false"
                            class="w-full h-8 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition flex items-center justify-center">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- 8. 58mm/80mm Thermal Receipt Container (Revealed exclusively during window.print) -->
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
                    <span class="font-bold uppercase" x-text="completedOrder?.payment?.method || 'CASH'"></span>
                </div>
                <template x-if="completedOrder?.payment?.method === 'CASH'">
                    <div>
                        <div class="flex justify-between">
                            <span>Bayar Tunai</span>
                            <span x-text="Number(completedOrder?.cashGiven || completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span>Kembalian</span>
                            <span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span>
                        </div>
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
    </div>
</body>
</html>
