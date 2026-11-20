<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SLL - Kasir POS Soto Lamongan Lestari</title>

    <!-- Brand Typography: Outfit & Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

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
<body class="h-full antialiased font-sans bg-[#FFF8F0] text-stone-800 select-none overflow-hidden"
      x-data="posTerminal()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar (Deep SLL Maroon with Golden Accents) -->
        <header class="h-16 bg-[#5B0A14] border-b border-[#7A0F1C] px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-brand-md z-30 text-white">
            <!-- Brand & Outlet Info -->
            <div class="flex items-center gap-3">
                <!-- SLL Golden 3D Badge -->
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#FFB800] via-[#FF8A00] to-[#E65100] text-white flex items-center justify-center font-display font-black text-base shadow-brand-orange ring-2 ring-[#FFC107]/60 shrink-0">
                    SLL
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm sm:text-base font-display font-black tracking-wide text-white leading-none">
                            SOTO LAMONGAN LESTARI
                        </h1>
                        <span class="hidden md:inline-block text-[9px] font-black px-1.5 py-0.5 rounded-full bg-[#FFB800] text-[#5B0A14] tracking-wider uppercase">
                            AUTHENTIC
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-amber-200/90 font-medium mt-0.5">
                        <span class="text-amber-100 font-semibold">{{ $outlet->name ?? 'Cabang Utama' }}</span>
                        <span class="text-amber-400/60">•</span>
                        <span class="text-[#FFC107] font-mono text-[11px] font-bold">📞 0852-1325-4219</span>
                    </div>
                </div>
            </div>

            <!-- Search Bar in Header (Min 44px Height, Shortcut [F3]) -->
            <div class="flex-1 max-w-md mx-4 lg:mx-8">
                <div class="relative">
                    <input type="text" 
                           id="pos-search-input"
                           x-model="searchQuery" 
                           placeholder="Cari soto, mie ayam, bakso, minuman... [F3 / /]" 
                           class="w-full bg-[#42060D] border border-[#7A0F1C] focus:bg-[#320409] text-white placeholder-amber-200/50 rounded-xl px-4 py-2.5 pl-10 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FF8A00] focus:border-[#FF8A00] transition shadow-inner">
                    <svg class="w-4 h-4 text-amber-300/70 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <template x-if="searchQuery">
                        <button type="button" @click="searchQuery = ''" class="absolute right-3 top-2.5 text-amber-300 hover:text-white text-xs font-bold">✕</button>
                    </template>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Online / Offline Status Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                     :class="isOnline ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/40' : 'bg-amber-950/80 text-amber-300 border border-amber-500/40'">
                    <span class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                    <span class="hidden sm:inline" x-text="isOnline ? (isSyncing ? 'Sinkron...' : 'Cloud Online') : 'Mode Offline'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-2 text-xs font-bold text-amber-100 bg-[#7A0F1C] hover:bg-[#911322] border border-amber-500/30 rounded-xl flex items-center gap-1.5 transition active:scale-95 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-[#FFB800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span class="hidden md:inline">Printer</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 text-xs font-black text-white bg-gradient-to-r from-[#FF8A00] to-[#E65100] hover:from-[#FFA000] hover:to-[#FF8A00] rounded-xl shadow-brand-orange transition active:scale-95 flex items-center gap-1">
                    <span>Backoffice</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Main Workspace: Split Catalog (Left) & Cart (Right) -->
        <main class="flex-1 flex overflow-hidden">
            <!-- Left: Catalog & Product Grid (Warm Off-White Canvas) -->
            <div class="flex-1 flex flex-col p-3.5 sm:p-5 overflow-hidden bg-[#FFF8F0]">
                
                <!-- Order Type & Categories Header Bar -->
                <div class="shrink-0 space-y-2.5 mb-3.5">
                    <!-- Order Type Selector Bar (Dine In / Take Away / GoFood / GrabFood / ShopeeFood) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <span class="text-[11px] font-black text-stone-500 uppercase tracking-wider mr-1 shrink-0">Tipe Pesanan:</span>
                        
                        <button type="button"
                                @click="orderType = 'DINE_IN'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5 shrink-0"
                                :class="orderType === 'DINE_IN' ? 'bg-[#5B0A14] text-white shadow-brand-maroon ring-2 ring-[#FF8A00]' : 'bg-white text-stone-700 border border-stone-200/80 hover:bg-stone-50'">
                            <span>🍲</span>
                            <span>Dine In (Makan Sini)</span>
                        </button>

                        <button type="button"
                                @click="orderType = 'TAKE_AWAY'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5 shrink-0"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-[#5B0A14] text-white shadow-brand-maroon ring-2 ring-[#FF8A00]' : 'bg-white text-stone-700 border border-stone-200/80 hover:bg-stone-50'">
                            <span>🛍️</span>
                            <span>Take Away (Bungkus)</span>
                        </button>

                        <button type="button"
                                @click="orderType = 'GOFOOD'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5 shrink-0"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-700 text-white shadow-sm ring-2 ring-emerald-400' : 'bg-white text-stone-700 border border-stone-200/80 hover:bg-stone-50'">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>GoFood</span>
                        </button>

                        <button type="button"
                                @click="orderType = 'GRABFOOD'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5 shrink-0"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-700 text-white shadow-sm ring-2 ring-green-400' : 'bg-white text-stone-700 border border-stone-200/80 hover:bg-stone-50'">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span>GrabFood</span>
                        </button>

                        <button type="button"
                                @click="orderType = 'SHOPEEFOOD'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5 shrink-0"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-700 text-white shadow-sm ring-2 ring-orange-400' : 'bg-white text-stone-700 border border-stone-200/80 hover:bg-stone-50'">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            <span>ShopeeFood</span>
                        </button>
                    </div>

                    <!-- Category Chips with Chunky Style -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button"
                                @click="activeCategory = 'ALL'"
                                class="px-4 py-2 rounded-xl text-xs font-display font-extrabold whitespace-nowrap transition flex items-center gap-1.5"
                                :class="activeCategory === 'ALL' ? 'bg-gradient-to-r from-[#FF8A00] to-[#E65100] text-white shadow-brand-orange ring-1 ring-[#FFB800]' : 'bg-white text-stone-700 border border-stone-200 hover:border-[#FF8A00] hover:text-[#5B0A14]'">
                            <span>🔥 Semua Menu SLL</span>
                        </button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button"
                                    @click="activeCategory = cat.id"
                                    class="px-4 py-2 rounded-xl text-xs font-display font-extrabold whitespace-nowrap transition"
                                    :class="activeCategory === cat.id ? 'bg-gradient-to-r from-[#FF8A00] to-[#E65100] text-white shadow-brand-orange ring-1 ring-[#FFB800]' : 'bg-white text-stone-700 border border-stone-200 hover:border-[#FF8A00] hover:text-[#5B0A14]'"
                                    x-text="cat.name">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- SLL Product Cards Grid (Fully Responsive, Never Squished or Truncated) -->
                <div class="flex-1 overflow-y-auto min-h-0 content-start auto-rows-max grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-4 p-1 pb-16">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="bg-white hover:bg-[#FFFDF9] border border-stone-200 hover:border-[#FF8A00] rounded-2xl p-3 flex flex-col justify-between shadow-brand-sm hover:shadow-brand-md cursor-pointer transition-all duration-150 active:scale-95 group min-h-[250px] relative">
                            
                            <div>
                                <!-- Thumbnail Box (Proper 128px Aspect Ratio, Rounded, Clean Border) -->
                                <div class="w-full h-32 rounded-xl bg-gradient-to-tr from-amber-50 to-orange-50/40 border border-amber-100 flex flex-col items-center justify-center mb-2.5 overflow-hidden shrink-0 group-hover:scale-[1.02] transition">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-3xl mb-1 drop-shadow-xs">
                                                <template x-if="product.name.toLowerCase().includes('soto')"><span>🍲</span></template>
                                                <template x-if="product.name.toLowerCase().includes('mie')"><span>🍜</span></template>
                                                <template x-if="product.name.toLowerCase().includes('bakso') && !product.name.toLowerCase().includes('mie')"><span>🥣</span></template>
                                                <template x-if="product.name.toLowerCase().includes('jeruk') || product.name.toLowerCase().includes('teh') || product.name.toLowerCase().includes('mineral')"><span>🍹</span></template>
                                                <template x-if="!product.name.toLowerCase().includes('soto') && !product.name.toLowerCase().includes('mie') && !product.name.toLowerCase().includes('bakso') && !product.name.toLowerCase().includes('jeruk') && !product.name.toLowerCase().includes('teh') && !product.name.toLowerCase().includes('mineral')"><span>🍗</span></template>
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Product Name (Chunky Display, Min 2 Lines for Uniform Grid) -->
                                <h3 class="text-xs sm:text-sm font-display font-black text-stone-900 group-hover:text-[#5B0A14] line-clamp-2 leading-snug min-h-[2.5rem]" x-text="product.name"></h3>
                                <span class="text-[10px] text-stone-400 font-mono mt-0.5 block" x-text="product.sku || 'Menu SLL'"></span>
                            </div>

                            <!-- Price & Quick Add Button (Min 44px Tap Target) -->
                            <div class="mt-2.5 pt-2 border-t border-stone-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs text-stone-400 font-bold block -mb-0.5">Harga</span>
                                    <span class="text-sm sm:text-base font-black text-[#E65100] font-mono tracking-tight">
                                        Rp<span x-text="Number(product.selling_price).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <button type="button" 
                                        class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#FF8A00] to-[#E65100] text-white flex items-center justify-center text-lg font-black shadow-brand-orange group-hover:scale-105 active:scale-95 transition shrink-0"
                                        title="Tambah ke Keranjang">
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: SLL Cart & Checkout Sidebar (Pinned, Crisp Clean White) -->
            <div class="w-80 md:w-96 bg-white border-l border-stone-200/90 flex flex-col h-full shrink-0 shadow-brand-md z-20">
                <!-- Cart Header -->
                <div class="p-3.5 sm:p-4 border-b border-stone-200 bg-[#FFFDF9] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-[#5B0A14] flex items-center justify-center font-black text-base shadow-xs">
                            🛒
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-display font-black text-stone-900">Pesanan Pelanggan</h2>
                            <div class="flex items-center gap-1.5 text-[11px] text-stone-500 font-medium">
                                <span class="font-bold text-[#E65100]" x-text="`${cart.length} item`"></span>
                                <span>•</span>
                                <span class="font-bold text-[#5B0A14]" x-text="orderType"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" 
                            @click="cart = []" 
                            x-show="cart.length > 0"
                            class="text-xs text-rose-600 hover:text-rose-700 font-extrabold px-2.5 py-1 rounded-lg hover:bg-rose-50 transition">
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5 min-h-0 bg-[#FAFAF8]">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-stone-400">
                            <span class="text-4xl mb-2 opacity-60">🍜</span>
                            <p class="text-xs font-bold text-stone-600">Keranjang Masih Kosong</p>
                            <p class="text-[11px] text-stone-400 mt-1 max-w-[200px]">Pilih menu soto, mie ayam, atau minuman di sebelah kiri untuk memulai.</p>
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="item.product_id">
                        <div class="bg-white rounded-xl p-3 border border-stone-200/90 shadow-2xs hover:border-amber-300 transition">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-display font-black text-stone-900 truncate" x-text="item.name"></h4>
                                    <span class="text-[11px] font-mono text-[#E65100] font-bold">
                                        Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <span class="text-xs font-mono font-black text-stone-900">
                                    Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <!-- Stepper & Notes Row -->
                            <div class="mt-2.5 pt-2 border-t border-stone-100 flex items-center justify-between">
                                <!-- Notes Input -->
                                <div class="flex-1 mr-2">
                                    <input type="text" 
                                           x-model="item.notes" 
                                           placeholder="Catatan: pedas/kuah pisah..." 
                                           class="w-full text-[10px] bg-stone-50 border border-stone-200 rounded-lg px-2 py-1 placeholder:text-stone-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#FF8A00]">
                                </div>

                                <!-- Quantity Stepper (Min 36px Tap Target) -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" 
                                            @click="updateQuantity(idx, -1)" 
                                            class="w-8 h-8 rounded-lg bg-stone-100 hover:bg-[#FF8A00] hover:text-white border border-stone-200 text-stone-700 flex items-center justify-center font-black text-sm active:scale-95 transition">
                                        -
                                    </button>
                                    <span class="text-xs font-black text-stone-900 w-6 text-center font-mono" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQuantity(idx, 1)" 
                                            class="w-8 h-8 rounded-lg bg-stone-100 hover:bg-[#FF8A00] hover:text-white border border-stone-200 text-stone-700 flex items-center justify-center font-black text-sm active:scale-95 transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Cart Pricing Calculation & Big Bayar Button -->
                <div class="p-4 border-t border-amber-200/60 bg-[#FFFDF9] space-y-2 shrink-0 shadow-lg">
                    <div class="flex justify-between text-xs text-stone-500 font-medium">
                        <span>Subtotal Tagihan</span>
                        <span class="font-mono text-stone-800 font-bold">Rp<span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-stone-500 font-medium">
                        <span>Pajak Resto (PPN 11%)</span>
                        <span class="font-mono text-stone-800 font-bold">Rp<span x-text="Number(taxAmount).toLocaleString('id-ID')"></span></span>
                    </div>
                    
                    <!-- Total Bayar Highlight Box -->
                    <div class="p-3 rounded-2xl bg-[#5B0A14] text-white flex justify-between items-center shadow-brand-maroon">
                        <div>
                            <span class="text-[10px] text-amber-300 font-black uppercase tracking-wider block">Total Pembayaran</span>
                            <span class="text-lg sm:text-xl font-display font-black text-[#FFB800] font-mono leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-xs font-black px-2 py-1 rounded bg-[#FF8A00] text-white" x-text="orderType"></span>
                    </div>

                    <!-- Large Bayar Button (Min 48px Height, F2 / Space) -->
                    <button type="button"
                            @click="paymentModalOpen = true; cashGiven = finalTotal"
                            :disabled="cart.length === 0"
                            class="w-full mt-2 py-3.5 rounded-2xl bg-gradient-to-r from-[#FF8A00] via-[#F97316] to-[#E65100] hover:from-[#FFA000] hover:to-[#FF8A00] disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-black text-sm tracking-wider uppercase shadow-brand-orange active:scale-[0.98] transition flex items-center justify-center gap-2">
                        <span>Lanjut ke Pembayaran</span>
                        <span class="text-xs bg-black/20 px-1.5 py-0.5 rounded font-mono">[F2]</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </main>

        <!-- Payment Modal (Crisp White Card with Deep Maroon & Golden Accents) -->
        <div x-show="paymentModalOpen" 
             class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-200" @click.outside="paymentModalOpen = false">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-stone-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#5B0A14] text-[#FFB800] flex items-center justify-center text-sm font-black shadow-xs">
                            💳
                        </div>
                        <div>
                            <h3 class="text-sm font-display font-black text-stone-900 tracking-wide">Pembayaran Kasir SLL</h3>
                            <span class="text-[11px] text-stone-400 font-medium">Tipe: <strong class="text-[#E65100]" x-text="orderType"></strong></span>
                        </div>
                    </div>
                    <button type="button" @click="paymentModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <!-- Total Due Box -->
                <div class="my-4 text-center bg-[#FFF8F0] p-4 rounded-2xl border border-amber-200/80">
                    <span class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total Tagihan Pesanan</span>
                    <div class="text-2xl sm:text-3xl font-display font-black text-[#5B0A14] font-mono mt-0.5">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Payment Methods Selector -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button"
                            @click="selectedPaymentMethod = 'CASH'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-black transition flex flex-col items-center gap-1 active:scale-95"
                            :class="selectedPaymentMethod === 'CASH' ? 'border-[#FF8A00] bg-orange-50 text-[#E65100] shadow-brand-orange ring-1 ring-[#FF8A00]' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                        <span>💵 Tunai (Cash)</span>
                    </button>
                    <button type="button"
                            @click="selectedPaymentMethod = 'QRIS'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-black transition flex flex-col items-center gap-1 active:scale-95"
                            :class="selectedPaymentMethod === 'QRIS' ? 'border-[#FF8A00] bg-orange-50 text-[#E65100] shadow-brand-orange ring-1 ring-[#FF8A00]' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                        <span>📱 QRIS Dinamis</span>
                    </button>
                    <button type="button"
                            @click="selectedPaymentMethod = 'TRANSFER'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-black transition flex flex-col items-center gap-1 active:scale-95"
                            :class="selectedPaymentMethod === 'TRANSFER' ? 'border-[#FF8A00] bg-orange-50 text-[#E65100] shadow-brand-orange ring-1 ring-[#FF8A00]' : 'border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100'">
                        <span>💳 Transfer Bank</span>
                    </button>
                </div>

                <!-- Cash Numpad / Cash Received -->
                <div x-show="selectedPaymentMethod === 'CASH'" class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold text-stone-700">Nominal Diterima</label>
                        <span class="text-[11px] text-stone-400">Uang Pas: <strong>Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span></strong></span>
                    </div>
                    <input type="number" 
                           x-model="cashGiven" 
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-base font-black text-stone-900 font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF8A00]">
                    
                    <!-- Quick Cash Denominations (Uang Pas, 10k, 20k, 50k, 100k, 200k) -->
                    <div class="grid grid-cols-4 gap-1.5 mt-2">
                        <button type="button" @click="cashGiven = finalTotal" class="py-2 px-2 rounded-xl bg-amber-100 hover:bg-amber-200 text-[11px] font-black text-[#5B0A14] border border-amber-300 transition active:scale-95">Uang Pas</button>
                        <button type="button" @click="cashGiven = 20000" class="py-2 px-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-[11px] font-bold text-stone-700 border border-stone-200 transition active:scale-95">Rp20.000</button>
                        <button type="button" @click="cashGiven = 50000" class="py-2 px-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-[11px] font-bold text-stone-700 border border-stone-200 transition active:scale-95">Rp50.000</button>
                        <button type="button" @click="cashGiven = 100000" class="py-2 px-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-[11px] font-bold text-stone-700 border border-stone-200 transition active:scale-95">Rp100.000</button>
                    </div>

                    <!-- Automatic Change Due Display -->
                    <div class="flex justify-between items-center text-xs mt-2.5 text-emerald-800 bg-emerald-50 p-3 rounded-2xl border border-emerald-200 font-semibold">
                        <span>Kembalian:</span>
                        <span class="font-display font-black text-emerald-700 text-base font-mono">
                            Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                <!-- Customer Info (Optional) -->
                <div class="space-y-2 mb-4">
                    <input type="text" x-model="customerName" placeholder="Nama Pelanggan (opsional)" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#FF8A00]">
                    <input type="text" x-model="customerPhone" placeholder="No. WhatsApp Pelanggan (opsional)" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#FF8A00]">
                </div>

                <!-- Checkout Action Button -->
                <button type="button"
                        @click="processCheckout()"
                        :disabled="isProcessingCheckout || (selectedPaymentMethod === 'CASH' && cashGiven < finalTotal)"
                        class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-[#FF8A00] via-[#F97316] to-[#E65100] hover:from-[#FFA000] hover:to-[#FF8A00] disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-black text-xs tracking-wider uppercase shadow-brand-orange active:scale-[0.98] transition flex items-center justify-center gap-2">
                    <svg x-show="isProcessingCheckout" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isProcessingCheckout ? 'Menyinkronkan ke Cloud...' : 'Selesaikan & Bayar (Lunas)'"></span>
                </button>
            </div>
        </div>

        <!-- Success Modal (Transaksi Berhasil SLL - Clean Thermal Receipt Preview) -->
        <div x-show="successModalOpen"
             class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white text-stone-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-stone-200 text-center animate-in fade-in zoom-in duration-200"
                 @click.outside="successModalOpen = false">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h3 class="text-lg font-display font-black text-stone-900 tracking-wide">Pesanan SLL Berhasil!</h3>
                <p class="text-xs text-stone-500 mt-1">Pembayaran lunas & otomatis tersinkron ke laporan omzet.</p>

                <div class="bg-stone-50 rounded-2xl p-4 my-4 text-left border border-stone-200 text-xs space-y-2">
                    <div class="flex justify-between items-center text-stone-500">
                        <span>No. Struk</span>
                        <span class="font-mono font-bold text-stone-900" x-text="completedOrder?.order_number || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>Metode Pembayaran</span>
                        <span class="font-bold text-[#E65100]" x-text="completedOrder?.payment?.method || 'CASH'"></span>
                    </div>
                    <div class="flex justify-between items-center text-stone-500">
                        <span>Total Pembayaran</span>
                        <span class="font-black text-stone-900 text-sm font-mono">Rp<span x-text="Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <template x-if="completedOrder?.payment?.method === 'CASH'">
                        <div class="space-y-1 pt-1.5 border-t border-stone-200">
                            <div class="flex justify-between items-center text-stone-500">
                                <span>Uang Tunai</span>
                                <span class="font-medium text-stone-800 font-mono">Rp<span x-text="Number(completedOrder?.cashGiven || 0).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between items-center text-emerald-700 font-bold">
                                <span>Kembalian</span>
                                <span class="font-mono">Rp<span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-2">
                    <button type="button"
                            @click="window.thermalPrinter && window.thermalPrinter.printReceipt(completedOrder)"
                            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-[#FF8A00] to-[#E65100] hover:from-[#FFA000] hover:to-[#FF8A00] text-white font-black text-xs shadow-brand-orange transition flex items-center justify-center gap-1.5 active:scale-95">
                        <span>🖨️ Cetak Struk Dapur / Kasir</span>
                    </button>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('dashboard') }}"
                           class="py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-200 font-bold text-xs transition flex items-center justify-center">
                            📊 Cek Omzet
                        </a>
                        <button type="button"
                                @click="successModalOpen = false"
                                class="py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-[#5B0A14] border border-amber-200 font-bold text-xs transition flex items-center justify-center">
                            🛒 Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
