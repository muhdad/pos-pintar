<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SLL - Soto Lamongan Lestari (Kasir POS)</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

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
<body class="h-full antialiased font-sans bg-gradient-to-br from-[#1c0404] via-[#2a0707] to-[#150202] text-amber-50 select-none overflow-hidden"
      x-data="posTerminal()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col">
        <!-- Top SLL Cashier Header Bar -->
        <header class="h-16 bg-[#250505]/95 backdrop-blur-md border-b border-amber-900/60 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-lg z-30">
            <!-- Brand & Outlet Info -->
            <div class="flex items-center gap-3">
                <!-- SLL Golden 3D Badge -->
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-amber-600 text-[#230404] flex items-center justify-center font-black text-base shadow-md shadow-orange-600/30 ring-2 ring-amber-400/80 shrink-0">
                    SLL
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm sm:text-base font-black tracking-wide bg-gradient-to-r from-amber-200 via-yellow-300 to-amber-400 bg-clip-text text-transparent leading-none">
                            SOTO LAMONGAN LESTARI
                        </h1>
                        <span class="hidden md:inline-block text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 tracking-wider">
                            AUTHENTIC
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-amber-200/60 font-medium mt-0.5">
                        <span>{{ $outlet->name ?? 'Cabang Utama' }}</span>
                        <span>•</span>
                        <span class="text-amber-400 font-mono text-[11px]">📞 0852-1325-4219</span>
                    </div>
                </div>
            </div>

            <!-- Search Bar in Header -->
            <div class="flex-1 max-w-md mx-4 lg:mx-8">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari soto, mie ayam, bakso, ceker, minuman..." 
                           class="w-full bg-[#180303]/90 border border-amber-900/60 rounded-full px-4 py-2 pl-9 text-xs text-amber-100 placeholder:text-amber-200/40 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-400 transition">
                    <svg class="w-4 h-4 text-amber-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2.5 sm:gap-3">
                <!-- Online / Offline Status Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                     :class="isOnline ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/40' : 'bg-amber-950/80 text-amber-300 border border-amber-500/40'">
                    <span class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                    <span class="hidden sm:inline" x-text="isOnline ? (isSyncing ? 'Sinkronisasi...' : 'Cloud Online') : 'Mode Offline'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-1.5 text-xs font-semibold text-amber-200 bg-[#340808] border border-amber-900/60 rounded-xl hover:bg-[#450c0c] hover:text-white flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span class="hidden md:inline">Printer</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 text-xs font-bold text-amber-900 bg-gradient-to-r from-amber-400 to-orange-400 hover:from-amber-300 hover:to-orange-300 rounded-xl shadow-sm shadow-orange-500/20 transition flex items-center gap-1">
                    <span>Backoffice</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Main Workspace: Split Catalog (Left) & Cart (Right) -->
        <main class="flex-1 flex overflow-hidden">
            <!-- Left: Catalog & Product Grid -->
            <div class="flex-1 flex flex-col p-4 sm:p-5 overflow-hidden">
                <!-- Categories Bar with SLL Styling -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2.5 mb-3 scrollbar-none shrink-0">
                    <button type="button"
                            @click="activeCategory = 'ALL'"
                            class="px-4 py-2 rounded-xl text-xs font-extrabold whitespace-nowrap transition flex items-center gap-1.5"
                            :class="activeCategory === 'ALL' ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white shadow-md shadow-orange-600/30 border border-amber-300' : 'bg-[#280707] text-amber-200/80 border border-amber-900/60 hover:bg-[#380b0b] hover:text-white'">
                        <span>🔥 Semua Menu SLL</span>
                    </button>
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button"
                                @click="activeCategory = cat.id"
                                class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition"
                                :class="activeCategory === cat.id ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white shadow-md shadow-orange-600/30 border border-amber-300' : 'bg-[#280707] text-amber-200/80 border border-amber-900/60 hover:bg-[#380b0b] hover:text-white'"
                                x-text="cat.name">
                        </button>
                    </template>
                </div>

                <!-- SLL Product Cards Grid -->
                <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 pr-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="bg-[#240606]/90 hover:bg-[#2e0909] backdrop-blur-md border border-amber-900/40 hover:border-amber-400/80 rounded-2xl p-3.5 flex flex-col justify-between shadow-md hover:shadow-orange-950/60 cursor-pointer transition active:scale-95 group relative overflow-hidden">
                            
                            <div>
                                <!-- Thumbnail Box -->
                                <div class="w-full h-24 rounded-xl bg-gradient-to-tr from-[#380b0b] via-[#480d0d] to-[#2d0707] border border-amber-900/50 flex flex-col items-center justify-center text-amber-400 mb-2.5 overflow-hidden group-hover:scale-[1.02] transition">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-3xl mb-1 drop-shadow-md">
                                                <template x-if="product.name.toLowerCase().includes('soto')"><span>🍲</span></template>
                                                <template x-if="product.name.toLowerCase().includes('mie')"><span>🍜</span></template>
                                                <template x-if="product.name.toLowerCase().includes('bakso') && !product.name.toLowerCase().includes('mie')"><span>🥣</span></template>
                                                <template x-if="product.name.toLowerCase().includes('jeruk') || product.name.toLowerCase().includes('teh') || product.name.toLowerCase().includes('mineral')"><span>🍹</span></template>
                                                <template x-if="!product.name.toLowerCase().includes('soto') && !product.name.toLowerCase().includes('mie') && !product.name.toLowerCase().includes('bakso') && !product.name.toLowerCase().includes('jeruk') && !product.name.toLowerCase().includes('teh') && !product.name.toLowerCase().includes('mineral')"><span>🍗</span></template>
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                <h3 class="text-xs font-bold text-amber-50 group-hover:text-amber-200 line-clamp-2 leading-snug" x-text="product.name"></h3>
                                <span class="text-[10px] text-amber-300/50 font-mono mt-0.5 block" x-text="product.sku || 'Menu SLL'"></span>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-amber-900/40 flex items-center justify-between">
                                <span class="text-sm font-black text-amber-400 font-mono">
                                    Rp<span x-text="Number(product.selling_price).toLocaleString('id-ID')"></span>
                                </span>
                                <span class="w-7 h-7 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white flex items-center justify-center text-sm font-black shadow-sm shadow-orange-600/30 group-hover:scale-110 group-hover:brightness-110 transition">
                                    +
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: SLL Cart & Checkout Sidebar (Alpine.js Controlled) -->
            <div class="w-80 md:w-96 bg-[#210505]/95 backdrop-blur-md border-l border-amber-900/60 flex flex-col h-full shrink-0 shadow-2xl z-20">
                <!-- Cart Header -->
                <div class="p-4 border-b border-amber-900/60 flex items-center justify-between bg-[#2a0606]">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🛒</span>
                        <div>
                            <h2 class="text-sm font-extrabold text-amber-100">Pesanan Pelanggan</h2>
                            <span class="text-[11px] text-amber-300/70" x-text="`${cart.length} item dipilih`"></span>
                        </div>
                    </div>
                    <button type="button" 
                            @click="cart = []" 
                            x-show="cart.length > 0"
                            class="text-xs text-rose-400 hover:text-rose-300 font-bold px-2 py-1 rounded-lg hover:bg-rose-950/40 transition">
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items List -->
                <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-amber-200/40 py-16 text-center">
                            <span class="text-4xl mb-2 opacity-50">🍲</span>
                            <p class="text-xs font-bold text-amber-200/70">Keranjang Masih Kosong</p>
                            <p class="text-[11px] text-amber-300/40 mt-1 max-w-[180px]">Pilih menu soto, mie ayam, atau minuman di sebelah kiri.</p>
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="item.product_id">
                        <div class="p-3 rounded-xl bg-[#2b0707] border border-amber-900/50 flex items-center justify-between gap-2 shadow-xs">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-amber-100 truncate" x-text="item.name"></h4>
                                <span class="text-[11px] text-amber-400 font-bold font-mono">
                                    Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="updateQuantity(idx, -1)" class="w-6 h-6 rounded-lg bg-[#3d0b0b] border border-amber-900/60 text-amber-200 flex items-center justify-center font-bold text-xs hover:bg-amber-600 hover:text-white transition">-</button>
                                <span class="text-xs font-black text-amber-100 w-5 text-center font-mono" x-text="item.quantity"></span>
                                <button type="button" @click="updateQuantity(idx, 1)" class="w-6 h-6 rounded-lg bg-[#3d0b0b] border border-amber-900/60 text-amber-200 flex items-center justify-center font-bold text-xs hover:bg-amber-600 hover:text-white transition">+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Cart Pricing Calculation & Checkout -->
                <div class="p-4 border-t border-amber-900/60 bg-[#280606] space-y-2">
                    <div class="flex justify-between text-xs text-amber-200/70">
                        <span>Subtotal Tagihan</span>
                        <span class="font-mono text-amber-100">Rp<span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-amber-200/70">
                        <span>Pajak Resto (PPN 11%)</span>
                        <span class="font-mono text-amber-100">Rp<span x-text="Number(taxAmount).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-sm font-bold text-amber-100 pt-2.5 border-t border-dashed border-amber-900/60">
                        <span>Total Bayar</span>
                        <span class="text-lg font-black text-amber-400 font-mono">
                            Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                        </span>
                    </div>

                    <!-- Checkout Button -->
                    <button type="button"
                            @click="paymentModalOpen = true; cashGiven = finalTotal"
                            :disabled="cart.length === 0"
                            class="w-full mt-3 py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-orange-600 to-amber-600 hover:from-amber-600 hover:to-orange-700 text-white font-black text-xs tracking-wider uppercase shadow-lg shadow-orange-600/25 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                        <span>Lanjut ke Pembayaran</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </main>

        <!-- Payment Modal (SLL Styled) -->
        <div x-show="paymentModalOpen" 
             class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-[#260606] text-amber-50 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-amber-900/80 animate-in fade-in zoom-in duration-200" @click.outside="paymentModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-amber-900/60">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs font-bold">💳</span>
                        <h3 class="text-sm font-black text-amber-100 tracking-wide">Pembayaran Pesanan SLL</h3>
                    </div>
                    <button type="button" @click="paymentModalOpen = false" class="text-amber-300/50 hover:text-white font-bold p-1">✕</button>
                </div>

                <div class="my-4 text-center bg-[#1c0404] p-3.5 rounded-2xl border border-amber-900/50">
                    <span class="text-xs text-amber-200/60 font-medium">Total Tagihan Pesanan</span>
                    <div class="text-2xl font-black text-amber-400 font-mono mt-0.5">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button"
                            @click="selectedPaymentMethod = 'CASH'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1"
                            :class="selectedPaymentMethod === 'CASH' ? 'border-amber-400 bg-amber-500/20 text-amber-300' : 'border-amber-900/60 bg-[#1c0404] text-amber-200/60 hover:bg-[#320808]'">
                        <span>💵 Tunai</span>
                    </button>
                    <button type="button"
                            @click="selectedPaymentMethod = 'QRIS'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1"
                            :class="selectedPaymentMethod === 'QRIS' ? 'border-amber-400 bg-amber-500/20 text-amber-300' : 'border-amber-900/60 bg-[#1c0404] text-amber-200/60 hover:bg-[#320808]'">
                        <span>📱 QRIS</span>
                    </button>
                    <button type="button"
                            @click="selectedPaymentMethod = 'TRANSFER'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1"
                            :class="selectedPaymentMethod === 'TRANSFER' ? 'border-amber-400 bg-amber-500/20 text-amber-300' : 'border-amber-900/60 bg-[#1c0404] text-amber-200/60 hover:bg-[#320808]'">
                        <span>💳 Transfer</span>
                    </button>
                </div>

                <!-- Cash Numpad / Cash Received -->
                <div x-show="selectedPaymentMethod === 'CASH'" class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold text-amber-200/80">Uang Diterima</label>
                        <span class="text-[11px] text-amber-300/60">Pas: <strong>Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span></strong></span>
                    </div>
                    <input type="number" 
                           x-model="cashGiven" 
                           class="w-full bg-[#1c0404] border border-amber-900/60 rounded-xl px-3 py-2 text-sm font-bold text-amber-300 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500">
                    
                    <!-- Quick Cash Denominations -->
                    <div class="grid grid-cols-4 gap-1.5 mt-2">
                        <button type="button" @click="cashGiven = finalTotal" class="py-1.5 px-2 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-[10px] font-bold text-amber-300 border border-amber-500/40 transition">Uang Pas</button>
                        <button type="button" @click="cashGiven = 50000" class="py-1.5 px-2 rounded-lg bg-[#360808] hover:bg-[#480d0d] text-[10px] font-bold text-amber-200 border border-amber-900/50 transition">Rp50.000</button>
                        <button type="button" @click="cashGiven = 100000" class="py-1.5 px-2 rounded-lg bg-[#360808] hover:bg-[#480d0d] text-[10px] font-bold text-amber-200 border border-amber-900/50 transition">Rp100.000</button>
                        <button type="button" @click="cashGiven = 200000" class="py-1.5 px-2 rounded-lg bg-[#360808] hover:bg-[#480d0d] text-[10px] font-bold text-amber-200 border border-amber-900/50 transition">Rp200.000</button>
                    </div>

                    <div class="flex justify-between items-center text-xs mt-2.5 text-amber-200 bg-emerald-950/60 p-2.5 rounded-xl border border-emerald-500/30">
                        <span class="font-medium text-emerald-300">Kembalian:</span>
                        <span class="font-black text-emerald-400 text-sm font-mono">Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span></span>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="space-y-2 mb-4">
                    <input type="text" x-model="customerName" placeholder="Nama Pelanggan (opsional)" class="w-full bg-[#1c0404] border border-amber-900/60 rounded-xl px-3 py-1.5 text-xs text-amber-100 placeholder:text-amber-200/40 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <input type="text" x-model="customerPhone" placeholder="No. WhatsApp Pelanggan (opsional)" class="w-full bg-[#1c0404] border border-amber-900/60 rounded-xl px-3 py-1.5 text-xs text-amber-100 placeholder:text-amber-200/40 focus:outline-none focus:ring-1 focus:ring-amber-500">
                </div>

                <!-- Action Button -->
                <button type="button"
                        @click="processCheckout()"
                        :disabled="isProcessingCheckout || (selectedPaymentMethod === 'CASH' && cashGiven < finalTotal)"
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-orange-600 to-amber-600 hover:from-amber-600 hover:to-orange-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs tracking-wider uppercase shadow-lg shadow-orange-600/30 transition flex items-center justify-center gap-2">
                    <svg x-show="isProcessingCheckout" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isProcessingCheckout ? 'Menyinkronkan ke Cloud...' : 'Selesaikan & Bayar (Lunas)'"></span>
                </button>
            </div>
        </div>

        <!-- Success Modal (Transaksi Berhasil SLL) -->
        <div x-show="successModalOpen"
             class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-[#260606] text-amber-50 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-amber-500/40 text-center animate-in fade-in zoom-in duration-200"
                 @click.outside="successModalOpen = false">
                <div class="w-16 h-16 bg-gradient-to-tr from-amber-500 to-orange-600 text-[#230404] rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-orange-600/30 ring-2 ring-amber-400">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h3 class="text-lg font-black text-amber-200 tracking-wide">Pesanan SLL Berhasil!</h3>
                <p class="text-xs text-amber-200/60 mt-1">Pembayaran lunas & otomatis tersinkron ke laporan omzet.</p>

                <div class="bg-[#1c0404] rounded-2xl p-4 my-4 text-left border border-amber-900/60 text-xs space-y-2">
                    <div class="flex justify-between items-center text-amber-200/60">
                        <span>No. Struk</span>
                        <span class="font-mono font-bold text-amber-200" x-text="completedOrder?.order_number || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center text-amber-200/60">
                        <span>Metode Pembayaran</span>
                        <span class="font-bold text-amber-400" x-text="completedOrder?.payment?.method || 'CASH'"></span>
                    </div>
                    <div class="flex justify-between items-center text-amber-200/60">
                        <span>Total Pembayaran</span>
                        <span class="font-black text-amber-400 text-sm font-mono">Rp<span x-text="Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <template x-if="completedOrder?.payment?.method === 'CASH'">
                        <div class="space-y-1 pt-1.5 border-t border-amber-900/60">
                            <div class="flex justify-between items-center text-amber-200/60">
                                <span>Uang Tunai</span>
                                <span class="font-medium text-amber-200 font-mono">Rp<span x-text="Number(completedOrder?.cashGiven || 0).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between items-center text-emerald-400 font-bold">
                                <span>Kembalian</span>
                                <span class="font-mono">Rp<span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-2">
                    <button type="button"
                            @click="window.thermalPrinter && window.thermalPrinter.printReceipt(completedOrder)"
                            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-[#230404] font-black text-xs shadow-md shadow-orange-600/25 transition flex items-center justify-center gap-1.5">
                        <span>🖨️ Cetak Struk Dapur / Kasir</span>
                    </button>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('dashboard') }}"
                           class="py-2.5 rounded-xl bg-[#360808] hover:bg-[#480d0d] text-amber-300 border border-amber-900/60 font-bold text-xs transition flex items-center justify-center">
                            📊 Cek Omzet
                        </a>
                        <button type="button"
                                @click="successModalOpen = false"
                                class="py-2.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 border border-amber-500/40 font-bold text-xs transition flex items-center justify-center">
                            🛒 Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
