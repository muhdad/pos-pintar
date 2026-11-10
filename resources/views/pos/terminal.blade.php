<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS Pintar') }} - Kasir</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

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
<body class="h-full antialiased font-sans bg-gradient-to-br from-[#e0f0fe] via-[#eaf4fe] to-[#f4f9ff] text-slate-800 select-none overflow-hidden"
      x-data="posTerminal()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar -->
        <header class="h-14 bg-white/85 backdrop-blur-md border-b border-blue-100 px-4 flex items-center justify-between shrink-0 shadow-sm z-30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/20">
                    P
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-none">POS Pintar</h1>
                    <span class="text-xs text-slate-500 font-medium">{{ $outlet->name ?? 'Semua Cabang' }}</span>
                </div>
            </div>

            <!-- Search Bar in Header -->
            <div class="flex-1 max-w-md mx-6">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari menu produk atau scan barcode..." 
                           class="w-full bg-slate-50/80 border border-blue-100 rounded-full px-4 py-1.5 pl-9 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3">
                <!-- Online / Offline Status Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                     :class="isOnline ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                    <span class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                    <span x-text="isOnline ? (isSyncing ? 'Sinkronisasi...' : 'Cloud Online') : 'Offline Mode (Local DB)'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printer</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition flex items-center gap-1">
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
            <div class="flex-1 flex flex-col p-4 overflow-hidden">
                <!-- Categories Bar -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-3 scrollbar-none shrink-0">
                    <button type="button"
                            @click="activeCategory = 'ALL'"
                            class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition"
                            :class="activeCategory === 'ALL' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'bg-white/80 text-slate-600 border border-blue-100 hover:bg-white'">
                        Semua Menu
                    </button>
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button"
                                @click="activeCategory = cat.id"
                                class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition"
                                :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'bg-white/80 text-slate-600 border border-blue-100 hover:bg-white'"
                                x-text="cat.name">
                        </button>
                    </template>
                </div>

                <!-- Product Cards Grid -->
                <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 pr-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="bg-white/90 backdrop-blur-sm border border-blue-100/70 hover:border-blue-400 rounded-2xl p-3 flex flex-col justify-between shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-md cursor-pointer transition active:scale-95 group">
                            <div>
                                <div class="w-full h-24 rounded-xl bg-gradient-to-tr from-blue-50 to-indigo-50/50 flex items-center justify-center text-blue-500 mb-2 overflow-hidden">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <svg class="w-8 h-8 opacity-40 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </template>
                                </div>
                                <h3 class="text-xs font-bold text-slate-900 line-clamp-2" x-text="product.name"></h3>
                                <p class="text-[10px] text-slate-400 mt-0.5" x-text="product.sku || 'Menu Reguler'"></p>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-blue-600">Rp<span x-text="Number(product.selling_price).toLocaleString('id-ID')"></span></span>
                                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold group-hover:bg-blue-600 group-hover:text-white transition">+</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: Cart & Checkout Sidebar (Alpine.js Controlled) -->
            <div class="w-80 md:w-96 bg-white/95 backdrop-blur-md border-l border-blue-100 flex flex-col h-full shrink-0 shadow-lg z-20">
                <!-- Cart Header -->
                <div class="p-4 border-b border-blue-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Keranjang Pesanan</h2>
                        <span class="text-xs text-slate-500" x-text="`${cart.length} item dipilih`"></span>
                    </div>
                    <button type="button" 
                            @click="cart = []" 
                            x-show="cart.length > 0"
                            class="text-xs text-rose-500 hover:text-rose-700 font-medium">
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items List -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-slate-400 py-12">
                            <svg class="w-12 h-12 mb-2 stroke-current opacity-30" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="text-xs font-medium">Keranjang masih kosong</p>
                            <p class="text-[11px] text-slate-400">Pilih menu di samping untuk mulai</p>
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="item.product_id">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-slate-900 truncate" x-text="item.name"></h4>
                                <span class="text-[11px] text-blue-600 font-semibold">Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="updateQuantity(idx, -1)" class="w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs hover:bg-slate-100">-</button>
                                <span class="text-xs font-bold text-slate-800 w-4 text-center" x-text="item.quantity"></span>
                                <button type="button" @click="updateQuantity(idx, 1)" class="w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs hover:bg-slate-100">+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Cart Pricing Calculation & Checkout -->
                <div class="p-4 border-t border-blue-100 bg-white/80 space-y-2">
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Subtotal</span>
                        <span>Rp<span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Pajak (PPN 11%)</span>
                        <span>Rp<span x-text="Number(taxAmount).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-dashed border-slate-200">
                        <span>Total Bayar</span>
                        <span class="text-blue-600">Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span></span>
                    </div>

                    <!-- Checkout Button -->
                    <button type="button"
                            @click="paymentModalOpen = true; cashGiven = finalTotal"
                            :disabled="cart.length === 0"
                            class="w-full mt-3 py-3 rounded-xl bg-blue-600 text-white font-bold text-xs tracking-wide shadow-md shadow-blue-500/25 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                        <span>Lanjut ke Pembayaran</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </main>

        <!-- Payment Modal -->
        <div x-show="paymentModalOpen" 
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-blue-100" @click.outside="paymentModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Pembayaran Pesanan</h3>
                    <button type="button" @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="my-4 text-center">
                    <span class="text-xs text-slate-500">Total Tagihan</span>
                    <div class="text-2xl font-black text-blue-600">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button"
                            @click="selectedPaymentMethod = 'CASH'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1"
                            :class="selectedPaymentMethod === 'CASH' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <span>💵 Tunai</span>
                    </button>
                    <button type="button"
                            @click="selectedPaymentMethod = 'QRIS'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1"
                            :class="selectedPaymentMethod === 'QRIS' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <span>📱 QRIS</span>
                    </button>
                    <button type="button"
                            @click="selectedPaymentMethod = 'TRANSFER'"
                            class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1"
                            :class="selectedPaymentMethod === 'TRANSFER' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <span>💳 EDC / Transfer</span>
                    </button>
                </div>

                <!-- Cash Numpad / Cash Received -->
                <div x-show="selectedPaymentMethod === 'CASH'" class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-medium text-slate-700">Uang Diterima</label>
                        <span class="text-[11px] text-slate-500">Tagihan: <strong>Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span></strong></span>
                    </div>
                    <input type="number" 
                           x-model="cashGiven" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    
                    <!-- Quick Cash Denominations -->
                    <div class="grid grid-cols-4 gap-1.5 mt-2">
                        <button type="button" @click="cashGiven = finalTotal" class="py-1.5 px-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-[10px] font-bold text-blue-700 border border-blue-200 transition">Uang Pas</button>
                        <button type="button" @click="cashGiven = 50000" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 transition">Rp50.000</button>
                        <button type="button" @click="cashGiven = 100000" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 transition">Rp100.000</button>
                        <button type="button" @click="cashGiven = 200000" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 transition">Rp200.000</button>
                    </div>

                    <div class="flex justify-between items-center text-xs mt-2.5 text-slate-600 bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-100">
                        <span class="font-medium text-emerald-800">Kembalian:</span>
                        <span class="font-black text-emerald-600 text-sm">Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span></span>
                    </div>
                </div>

                <!-- Customer Info for WA Receipt -->
                <div class="space-y-2 mb-4">
                    <input type="text" x-model="customerName" placeholder="Nama Pelanggan (opsional)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700">
                    <input type="text" x-model="customerPhone" placeholder="No. WhatsApp Pelanggan (opsional)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700">
                </div>

                <!-- Action Button -->
                <button type="button"
                        @click="processCheckout()"
                        :disabled="isProcessingCheckout || (selectedPaymentMethod === 'CASH' && cashGiven < finalTotal)"
                        class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs tracking-wide shadow-md shadow-emerald-500/25 transition flex items-center justify-center gap-2">
                    <svg x-show="isProcessingCheckout" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isProcessingCheckout ? 'Menyinkronkan ke Omzet Cloud...' : 'Selesaikan & Bayar (Lunas)'"></span>
                </button>
            </div>
        </div>

        <!-- Success Modal (Transaksi Berhasil & Tersinkron Omzet) -->
        <div x-show="successModalOpen"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-emerald-100 text-center animate-in fade-in zoom-in duration-200"
                 @click.outside="successModalOpen = false">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h3 class="text-lg font-black text-slate-900">Transaksi Berhasil!</h3>
                <p class="text-xs text-slate-500 mt-1">Pesanan lunas & otomatis tersinkron ke laporan omzet cloud.</p>

                <div class="bg-slate-50 rounded-2xl p-4 my-4 text-left border border-slate-100 text-xs space-y-2">
                    <div class="flex justify-between items-center text-slate-500">
                        <span>No. Transaksi</span>
                        <span class="font-mono font-bold text-slate-800" x-text="completedOrder?.order_number || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Metode Pembayaran</span>
                        <span class="font-bold text-blue-600" x-text="completedOrder?.payment?.method || 'CASH'"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Total Pembayaran</span>
                        <span class="font-black text-slate-900 text-sm">Rp<span x-text="Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <template x-if="completedOrder?.payment?.method === 'CASH'">
                        <div class="space-y-1 pt-1.5 border-t border-slate-200/60">
                            <div class="flex justify-between items-center text-slate-500">
                                <span>Uang Tunai</span>
                                <span class="font-medium text-slate-700">Rp<span x-text="Number(completedOrder?.cashGiven || 0).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="flex justify-between items-center text-emerald-600 font-bold">
                                <span>Kembalian</span>
                                <span>Rp<span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-2">
                    <button type="button"
                            @click="window.thermalPrinter && window.thermalPrinter.printReceipt(completedOrder)"
                            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                        <span>🖨️ Cetak Struk Fisik</span>
                    </button>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('dashboard') }}"
                           class="py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold text-xs transition flex items-center justify-center">
                            📊 Cek Omzet
                        </a>
                        <button type="button"
                                @click="successModalOpen = false"
                                class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition flex items-center justify-center">
                            🛒 Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

