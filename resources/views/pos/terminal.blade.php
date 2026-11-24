<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SLL POS - Kasir Soto Lamongan Lestari</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

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
<body class="h-full antialiased font-sans bg-[#F8FAFC] text-slate-800 select-none overflow-hidden"
      x-data="posTerminal()"
      x-init="init()">

    <!-- Wrapper seluruh UI Kasir POS (Sembunyi Total saat Mode Print agar tidak memakan tinggi kertas 58mm) -->
    <div id="pos-app-ui" class="h-screen w-screen flex flex-col overflow-hidden">
        <!-- 1. Top Bar: SLL Clean Rail with Shift Controls & Open Bills -->
        <header class="h-14 bg-[#0F172A] border-b border-slate-800 px-2.5 sm:px-4 flex items-center justify-between shrink-0 text-white z-30">
            <!-- Brand & Outlet Info -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    🍜
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="font-bold text-xs tracking-wide text-white leading-tight truncate max-w-[120px] sm:max-w-none">SOTO LAMONGAN LESTARI</span>
                        <span class="hidden sm:inline-block text-[9px] font-semibold px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono shrink-0">KASIR 0ms</span>
                    </div>
                    <div class="text-[10px] sm:text-[11px] text-slate-400 font-mono truncate flex items-center gap-1 max-w-[140px] sm:max-w-none">
                        <span class="truncate">{{ $outlet->name ?? 'Cabang Utama' }}</span>
                        <span class="text-slate-600 hidden sm:inline">•</span>
                        <template x-if="activeShift">
                            <span class="text-emerald-400 font-semibold hidden sm:inline">Shift Aktif</span>
                        </template>
                        <template x-if="!activeShift">
                            <span class="text-amber-300 font-medium cursor-pointer hover:underline" @click="startShiftModalOpen = true">Buka Shift</span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <!-- Open Bills Counter Badge -->
                <button type="button" 
                        @click="openBillsModalOpen = true"
                        class="px-2 py-1.5 text-xs font-semibold rounded-xl transition flex items-center gap-1 border shrink-0"
                        :class="openBills.length > 0 ? 'bg-amber-500/20 border-amber-400/40 text-amber-200' : 'bg-slate-800 border-slate-700 text-slate-300 hover:text-white'">
                    <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="hidden sm:inline">Tagihan</span>
                    <span class="px-1.5 py-0.2 rounded-lg text-[10px] font-bold font-mono"
                          :class="openBills.length > 0 ? 'bg-amber-400 text-slate-900' : 'bg-slate-700 text-slate-300'"
                          x-text="openBills.length"></span>
                </button>

                <!-- Shift Management Actions -->
                <template x-if="activeShift">
                    <div class="hidden md:flex items-center gap-1.5">
                        <button type="button" 
                                @click="pettyCashModalOpen = true"
                                class="px-2.5 py-1.5 text-xs font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Kas Keluar</span>
                        </button>

                        <button type="button" 
                                @click="closeShiftModalOpen = true"
                                class="px-2.5 py-1.5 text-xs font-medium text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-1">
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
                            class="px-2.5 py-1.5 text-xs font-semibold text-amber-900 bg-amber-400 hover:bg-amber-300 rounded-xl transition shadow-xs">
                        Buka Shift
                    </button>
                </template>

                <!-- Status Koneksi -->
                <div class="flex items-center gap-1.5 px-2 py-1.5 rounded-xl text-xs font-semibold shrink-0"
                     :class="isOnline ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : 'bg-amber-500/15 border border-amber-500/30 text-amber-300'"
                     :title="isOnline ? 'Terhubung ke cloud backend' : 'Mode offline lokal'">
                    <span class="w-2 h-2 rounded-full shrink-0" :class="isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                    <span class="hidden sm:inline" x-text="isOnline ? (isSyncing ? 'Sinkron...' : 'Online') : 'Offline'"></span>
                </div>

                <!-- Bluetooth & USB Thermal Printer Direct Connect -->
                <div x-data="{ printerMenuOpen: false }" class="relative">
                    <button type="button" 
                            @click="printerMenuOpen = !printerMenuOpen"
                            class="p-2 sm:px-2.5 sm:py-1.5 text-xs font-semibold rounded-xl flex items-center gap-1.5 transition active:translate-y-px shrink-0 border"
                            :class="window.thermalPrinter?.isConnected ? 'bg-emerald-500/20 border-emerald-400/60 text-emerald-200' : 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-200'"
                            title="Pengaturan Auto-Print & Koneksi Printer">
                        <svg class="w-3.5 h-3.5" :class="window.thermalPrinter?.isConnected ? 'text-emerald-400' : 'text-amber-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span class="hidden lg:inline" x-text="window.thermalPrinter?.isConnected ? (window.thermalPrinter.printerName || 'Printer Siap') : 'Printer Thermal'"></span>
                    </button>

                    <!-- Popover Dropdown -->
                    <div x-show="printerMenuOpen" 
                         @click.outside="printerMenuOpen = false" 
                         x-cloak 
                         class="absolute right-0 mt-2 w-72 bg-white text-slate-800 rounded-2xl shadow-xl border border-slate-200 p-3.5 z-50 text-xs space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="font-bold text-slate-800">Printer Thermal Kasir</span>
                            <span class="text-[10px] px-2 py-0.5 rounded font-mono font-bold" 
                                  :class="window.thermalPrinter?.useRawBt ? 'bg-amber-100 text-amber-800' : (window.thermalPrinter?.isConnected ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600')"
                                  x-text="window.thermalPrinter?.useRawBt ? 'RAWBT AKTIF' : (window.thermalPrinter?.isConnected ? 'TERHUBUNG' : 'BELUM TERHUBUNG')"></span>
                        </div>

                        <!-- Mode RawBT Rekomendasi Android -->
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200/80 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-900">Mode RawBT (Android)</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           :checked="window.thermalPrinter?.useRawBt" 
                                           @change="window.thermalPrinter && window.thermalPrinter.toggleRawBt($event.target.checked)" 
                                           class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                                </label>
                            </div>
                            <p class="text-[10px] text-amber-800 leading-snug">
                                ⭐ <strong>Rekomendasi Utama Tablet Android</strong>: Mendukung SEMUA merk printer Bluetooth 58mm (Bluetooth Classic SPP & BLE) via aplikasi RawBT resmi di Play Store.
                            </p>
                        </div>

                        <p class="text-[11px] text-slate-500">
                            Atau sambungkan langsung via Web Bluetooth / USB OTG:
                        </p>

                        <div class="space-y-1.5">
                            <button type="button" 
                                    @click="window.thermalPrinter && window.thermalPrinter.connectBluetooth(); printerMenuOpen = false" 
                                    class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center justify-center gap-2 transition active:scale-95">
                                <span>📶</span>
                                <span>Hubungkan Bluetooth (BLE)</span>
                            </button>

                            <button type="button" 
                                    @click="window.thermalPrinter && window.thermalPrinter.connectUsb(); printerMenuOpen = false" 
                                    class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs flex items-center justify-center gap-2 transition active:scale-95">
                                <span>🔌</span>
                                <span>Hubungkan Kabel USB (OTG)</span>
                            </button>
                        </div>

                        <!-- Bantuan Bluetooth Android -->
                        <div x-data="{ helpOpen: false }" class="pt-1">
                            <button type="button" @click="helpOpen = !helpOpen" class="text-[10px] text-blue-600 hover:text-blue-800 font-medium underline flex items-center gap-1">
                                <span>❓ Bluetooth tidak terdeteksi di Android?</span>
                            </button>
                            <div x-show="helpOpen" class="mt-1.5 p-2 bg-slate-50 rounded-xl text-[9px] text-slate-600 space-y-1 border border-slate-200">
                                <p><strong>1. GPS / Lokasi Wajib Aktif:</strong> Android mewajibkan Lokasi di quick settings aktif agar Chrome diizinkan scan Bluetooth.</p>
                                <p><strong>2. Printer Bluetooth Classic:</strong> Mayoritas printer kasir 58mm murah di Indonesia adalah Bluetooth Classic (SPP). Aktifkan <em>Mode RawBT</em> di atas agar terhubung 100%.</p>
                                <p><strong>3. Chrome HTTP Flag:</strong> Di Chrome tablet buka <code class="text-slate-800">chrome://flags</code> -> cari <em>unsafely-treat-insecure-origin-as-secure</em> -> masukkan URL ini.</p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 space-y-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" :checked="autoPrintReceipt" @change="toggleAutoPrint()" class="rounded text-emerald-600 focus:ring-emerald-400 w-4 h-4">
                                <span class="text-[11px] font-bold text-slate-700">Auto-Print Saat Bayar Lunas</span>
                            </label>
                            <span class="text-[10px] text-slate-400 block">
                                💡 Tip Chrome Silent Print: Jalankan Chrome dengan parameter <code class="bg-slate-100 px-1 rounded font-mono text-slate-700">--kiosk-printing</code> untuk mencetak instan tanpa dialog browser.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Struk Terakhir (Cetak Ulang Struk di Tablet) -->
                <button type="button" 
                        @click="openLastReceipt()"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition shadow-xs flex items-center gap-1.5 shrink-0 active:scale-95"
                        title="Buka atau Cetak Ulang Struk Transaksi Terakhir">
                    <span>🧾</span>
                    <span class="hidden md:inline">Buka Struk</span>
                </button>

                <!-- Backoffice Link -->
                <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1 shrink-0">
                    <span class="hidden sm:inline">Backoffice</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- 2. Workspace: Catalog Center + Pinned Order Panel Right (>= lg) -->
        <main class="flex-1 flex overflow-hidden relative">
            <!-- Center: Product Catalog -->
            <div class="flex-1 w-full flex flex-col p-3 sm:p-4 overflow-hidden bg-[#F8FAFC]">
                
                <!-- Search & Category Header -->
                <div class="shrink-0 space-y-2.5 mb-3">
                    <!-- Search Bar (F3) -->
                    <div class="relative">
                        <input type="text" 
                                id="pos-search-input"
                                x-model="searchQuery" 
                                placeholder="Cari menu soto, mie ayam, bakso, minuman... (F3 / /)" 
                                class="w-full bg-white border border-slate-200/90 rounded-xl px-3.5 py-2 pl-9 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400 shadow-xs transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <template x-if="searchQuery">
                            <button type="button" @click="searchQuery = ''" class="absolute right-3 top-2 text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                        </template>
                    </div>

                    <!-- Category Chips (Pastel Pills) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none touch-pan-x">
                        <button type="button"
                                @click="activeCategory = 'ALL'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition shadow-xs"
                                :class="activeCategory === 'ALL' ? 'bg-emerald-600 text-white font-bold' : 'bg-white text-slate-600 border border-slate-200/80 hover:bg-slate-50'">
                            Semua Menu
                        </button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button"
                                    @click="activeCategory = cat.id"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition shadow-xs"
                                    :class="activeCategory === cat.id ? 'bg-emerald-600 text-white font-bold' : 'bg-white text-slate-600 border border-slate-200/80 hover:bg-slate-50'"
                                    x-text="cat.name">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Product Tiles Grid: Clean, Soft Slate & Mint Accents (pb-36 on mobile to avoid bottom cart obstruction) -->
                <div class="flex-1 overflow-y-auto min-h-0 content-start auto-rows-max grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-2.5 p-0.5 pb-36 lg:pb-8">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)"
                             class="rounded-2xl p-3 flex flex-col justify-between transition group min-h-[100px] sm:min-h-[108px] relative select-none cursor-pointer shadow-xs"
                             :class="isProductOutOfStock(product) 
                                ? 'bg-slate-50 border border-slate-200/60 opacity-60' 
                                : 'bg-white border border-slate-200/80 hover:border-emerald-300 hover:shadow-sm active:scale-[0.98]'">
                            
                            <div>
                                <!-- SKU / Category & Stock Status -->
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-mono font-medium uppercase tracking-wider text-slate-400 truncate" x-text="product.sku || 'Menu SLL'"></span>
                                    <template x-if="isProductOutOfStock(product)">
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-md bg-rose-50 text-rose-600 border border-rose-200 uppercase" :title="getOutOfStockReason(product)">Habis</span>
                                    </template>
                                    <template x-if="!isProductOutOfStock(product)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 group-hover:bg-emerald-500 transition shrink-0"></span>
                                    </template>
                                </div>

                                <!-- Product Name -->
                                <h3 class="text-xs sm:text-sm font-semibold text-slate-800 group-hover:text-emerald-700 line-clamp-2 leading-tight" x-text="product.name"></h3>
                            </div>

                            <!-- Price & Add Button -->
                            <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs sm:text-sm font-bold text-emerald-700 font-mono tabular-nums block">
                                        Rp<span x-text="Number(getProductPrice(product)).toLocaleString('id-ID')"></span>
                                    </span>
                                    <template x-if="priceMultiplier !== 1.0">
                                        <span class="text-[9px] text-emerald-600 font-semibold block leading-none">Harga Ojol (+20%)</span>
                                    </template>
                                </div>
                                <button type="button" 
                                        class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold transition active:translate-y-px shrink-0"
                                        :class="isProductOutOfStock(product) 
                                            ? 'bg-slate-100 text-slate-400' 
                                            : 'bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white'"
                                        title="Tambah item">
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- 3. Desktop & Tablet Landscape: Pinned Order Panel (>= lg) -->
            <div class="hidden lg:flex lg:w-80 xl:w-90 2xl:w-96 bg-white border-l border-slate-200/80 flex-col h-full shrink-0 z-20 shadow-xs">
                <!-- Panel Header -->
                <div class="p-3 border-b border-slate-200/80 bg-slate-50/70 space-y-2 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pesanan Saat Ini</h2>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg bg-slate-200 text-slate-700 font-mono" x-text="`${cart.length} item`"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="holdCurrentOrder()" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-amber-800 hover:text-amber-900 font-semibold px-2 py-0.5 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 transition"
                                    title="Simpan pesanan makan dulu bayar belakangan (F4)">
                                Hold [F4]
                            </button>
                            <button type="button" 
                                    @click="cart = []; currentOpenBillId = null; selectedTableId = ''; selectedTableName = ''" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-rose-600 hover:text-rose-700 font-semibold px-2 py-0.5 rounded-lg hover:bg-rose-50 transition">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Order Type Selector (Dine-In, Bungkus, GoFood, Grab, Shopee) -->
                    <div class="grid grid-cols-5 gap-1 text-[10px] font-semibold">
                        <button type="button"
                                @click="setOrderType('DINE_IN')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'DINE_IN' ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Dine In
                        </button>
                        <button type="button"
                                @click="setOrderType('TAKE_AWAY')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Bungkus
                        </button>
                        <button type="button"
                                @click="setOrderType('GOFOOD')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            GoFood
                        </button>
                        <button type="button"
                                @click="setOrderType('GRABFOOD')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-600 text-white border-green-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Grab
                        </button>
                        <button type="button"
                                @click="setOrderType('SHOPEEFOOD')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-600 text-white border-orange-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Shopee
                        </button>
                    </div>

                    <!-- Dine In Table Selector (Meja 01 - Meja 12) -->
                    <template x-if="orderType === 'DINE_IN'">
                        <div class="pt-1.5 border-t border-slate-200">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Pilih Meja Resto</label>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-1 max-h-20 overflow-y-auto">
                                <template x-for="tbl in tables" :key="tbl.id">
                                    <button type="button"
                                            @click="selectTable(tbl)"
                                            class="py-1 px-1 rounded-lg text-[10px] font-mono font-bold text-center border transition"
                                            :class="selectedTableId === tbl.id 
                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' 
                                                : (tbl.is_occupied ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50')"
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
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400">
                            <p class="text-xs font-medium text-slate-500">Belum ada item pesanan</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Pilih menu dari katalog di sebelah kiri.</p>
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="item.product_id">
                        <div class="bg-slate-50/70 rounded-xl p-2.5 border border-slate-200/70 space-y-1.5">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-semibold text-slate-800 truncate" x-text="item.name"></h4>
                                    <span class="text-[10px] font-mono tabular-nums text-slate-500">
                                        @Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-slate-800 font-mono tabular-nums">
                                    Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <!-- Stepper & Notes Row -->
                            <div class="pt-1 border-t border-slate-200/60 flex items-center justify-between gap-2">
                                <input type="text" 
                                       x-model="item.notes" 
                                       placeholder="Catatan: kuah pisah, koya banyak..." 
                                       class="flex-1 text-[10px] bg-white border border-slate-200 rounded-lg px-2 py-0.5 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-400">

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" 
                                            @click="updateQuantity(idx, -1)" 
                                            class="w-6 h-6 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs transition active:translate-y-px">
                                        -
                                    </button>
                                    <span class="text-xs font-bold text-slate-800 w-5 text-center font-mono tabular-nums" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQuantity(idx, 1)" 
                                            class="w-6 h-6 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs transition active:translate-y-px">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Totals & Checkout / Hold / KOT Buttons -->
                <div class="p-3 border-t border-slate-200/80 bg-slate-50/80 space-y-2 shrink-0">
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Subtotal</span>
                        <span class="font-mono tabular-nums font-semibold text-slate-700">Rp<span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                    </div>
                    
                    <!-- Total Bayar Block -->
                    <div class="p-3 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white flex justify-between items-center shadow-xs">
                        <div>
                            <span class="text-[10px] text-slate-300 uppercase tracking-wider font-semibold block">
                                Total Tagihan <span x-show="selectedTableName" x-text="`(${selectedTableName})`"></span>
                            </span>
                            <span class="text-lg font-bold text-emerald-400 font-mono tabular-nums leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-white/10 text-slate-200" x-text="orderType"></span>
                    </div>

                    <!-- Action Grid: KOT & Hold Order -->
                    <div class="grid grid-cols-2 gap-2 pt-0.5">
                        <button type="button"
                                @click="printKitchenTicket()"
                                :disabled="cart.length === 0"
                                class="h-9 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 disabled:opacity-40 text-slate-700 font-semibold text-[11px] transition flex items-center justify-center gap-1 active:translate-y-px shadow-xs">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Tiket Dapur</span>
                        </button>

                        <button type="button"
                                @click="holdCurrentOrder()"
                                :disabled="cart.length === 0 || isHoldingOrder"
                                class="h-9 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-semibold text-[11px] transition flex items-center justify-center gap-1 active:translate-y-px shadow-xs">
                            <span x-text="isHoldingOrder ? 'Menyimpan...' : 'Hold Bill (F4)'"></span>
                        </button>
                    </div>

                    <!-- Dominant Bayar Button (F2) -->
                    <button type="button"
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0"
                            class="w-full h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs tracking-wider uppercase transition shadow-xs flex items-center justify-center gap-1.5 active:translate-y-px">
                        <span>Bayar Sekarang</span>
                        <span class="text-[10px] bg-black/20 px-1.5 py-0.5 rounded-md font-mono font-normal">F2</span>
                    </button>
                </div>
            </div>
        </main>

        <!-- 4. Mobile / Tablet Portrait: Floating Bottom Bar (< lg) -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 px-3.5 py-2.5 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] z-40 flex items-center justify-between gap-3"
             style="padding-bottom: max(0.625rem, env(safe-area-inset-bottom));">
            <div class="flex items-center gap-2.5 min-w-0 cursor-pointer" @click="mobileCartOpen = true">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 relative shadow-xs">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span x-show="cart.length > 0" 
                          class="absolute -top-1.5 -right-1.5 bg-emerald-600 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-4 text-center border-2 border-white font-mono" 
                          x-text="cart.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)"></span>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block" x-text="`${cart.length} item • ${orderType}`"></span>
                    <span class="text-sm font-bold text-slate-800 font-mono tabular-nums block truncate">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        @click="mobileCartOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs tracking-wide uppercase transition shadow-xs flex items-center gap-1.5 active:translate-y-px">
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
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity"
                 @click="mobileCartOpen = false"></div>

            <div class="relative bg-white rounded-t-3xl max-h-[88vh] flex flex-col w-full border-t border-slate-200 shadow-2xl z-10 animate-in slide-in-from-bottom duration-200"
                 style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));"
                 @click.stop>
                <!-- Drawer Header -->
                <div class="pt-3 pb-2.5 px-4 border-b border-slate-200/80 bg-slate-50/70 shrink-0">
                    <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto mb-2.5"></div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pesanan Saat Ini</h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-slate-200 text-slate-700 font-mono" x-text="`${cart.length} item`"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="holdCurrentOrder()" 
                                    x-show="cart.length > 0"
                                    class="text-[11px] text-amber-800 font-semibold px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 transition">
                                Hold [F4]
                            </button>
                            <button type="button" 
                                    @click="mobileCartOpen = false" 
                                    class="p-1 rounded-lg text-slate-400 hover:text-slate-700 text-sm font-bold">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Order Type Selector -->
                    <div class="grid grid-cols-5 gap-1 text-[10px] font-semibold mt-2.5">
                        <button type="button"
                                @click="setOrderType('DINE_IN')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'DINE_IN' ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Dine In
                        </button>
                        <button type="button"
                                @click="setOrderType('TAKE_AWAY')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'TAKE_AWAY' ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Bungkus
                        </button>
                        <button type="button"
                                @click="setOrderType('GOFOOD')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'GOFOOD' ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            GoFood
                        </button>
                        <button type="button"
                                @click="setOrderType('GRABFOOD')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'GRABFOOD' ? 'bg-green-600 text-white border-green-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Grab
                        </button>
                        <button type="button"
                                @click="setOrderType('SHOPEEFOOD')"
                                class="py-1.5 rounded-xl border text-center transition"
                                :class="orderType === 'SHOPEEFOOD' ? 'bg-orange-600 text-white border-orange-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                            Shopee
                        </button>
                    </div>

                    <!-- Dine-In Table Picker -->
                    <template x-if="orderType === 'DINE_IN'">
                        <div class="pt-2 border-t border-slate-200 mt-2">
                            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Pilih Meja:</span>
                            <div class="flex gap-1 overflow-x-auto pb-1">
                                <template x-for="tbl in tables" :key="tbl.id">
                                    <button type="button"
                                            @click="selectTable(tbl)"
                                            class="py-1 px-2.5 rounded-lg text-[10px] font-bold whitespace-nowrap border"
                                            :class="selectedTableId === tbl.id ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-700 border-slate-200'"
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
                        <div class="bg-slate-50/70 rounded-xl p-2.5 border border-slate-200/70 space-y-1.5">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-semibold text-slate-800 truncate" x-text="item.name"></h4>
                                    <span class="text-[10px] font-mono tabular-nums text-slate-500">
                                        @Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-slate-800 font-mono tabular-nums">
                                    Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <div class="pt-1 border-t border-slate-200/60 flex items-center justify-between gap-2">
                                <input type="text" 
                                       x-model="item.notes" 
                                       placeholder="Catatan menu..." 
                                       class="flex-1 text-[10px] bg-white border border-slate-200 rounded-lg px-2 py-0.5 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-400">

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" 
                                            @click="updateQuantity(idx, -1)" 
                                            class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                        -
                                    </button>
                                    <span class="text-xs font-bold text-slate-800 w-5 text-center font-mono tabular-nums" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQuantity(idx, 1)" 
                                            class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Drawer Totals & Bayar Button -->
                <div class="p-3 border-t border-slate-200 bg-slate-50/80 space-y-2 shrink-0">
                    <div class="p-3 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white flex justify-between items-center shadow-xs">
                        <div>
                            <span class="text-[10px] text-slate-300 uppercase tracking-wider font-semibold block">Total Tagihan</span>
                            <span class="text-lg font-bold text-emerald-400 font-mono tabular-nums leading-none">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-white/10 text-slate-200" x-text="orderType"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                @click="printKitchenTicket()"
                                class="h-10 rounded-xl bg-white border border-slate-200 font-semibold text-xs text-slate-700 shadow-xs">
                            Tiket Dapur
                        </button>
                        <button type="button"
                                @click="mobileCartOpen = false; openPaymentModal()"
                                class="h-10 rounded-xl bg-emerald-600 text-white font-bold text-xs uppercase tracking-wide shadow-xs">
                            Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Payment Modal (Tunai, QRIS, Transfer, Multi-Payment Split Bill) -->
        <div x-show="paymentModalOpen" 
             @click.self="paymentModalOpen = false"
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-slate-800 rounded-t-3xl sm:rounded-3xl max-w-sm sm:max-w-md w-full border border-slate-200/90 shadow-xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in duration-150" 
                 @click.stop>
                <!-- Header -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Pembayaran Kasir</h3>
                        <span class="text-[11px] text-slate-500">Tipe: <strong class="text-emerald-700" x-text="orderType"></strong> <span x-show="selectedTableName" x-text="`• ${selectedTableName}`"></span></span>
                    </div>
                    <button type="button" @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-4 overflow-y-auto flex-1 min-h-0 space-y-3.5">
                    <!-- Total Due Box -->
                    <div class="text-center bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Total Tagihan</span>
                        <div class="text-2xl font-bold text-slate-800 font-mono tabular-nums mt-0.5">
                            Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- Payment Method Tabs -->
                    <div class="grid grid-cols-4 gap-1.5 text-[11px] font-semibold">
                        <button type="button"
                                @click="selectedPaymentMethod = 'CASH'"
                                class="py-2 rounded-xl border text-center transition shadow-xs"
                                :class="selectedPaymentMethod === 'CASH' ? 'border-emerald-600 bg-emerald-600 text-white font-bold' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'">
                            Tunai
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'QRIS'; generateQris();"
                                class="py-2 rounded-xl border text-center transition shadow-xs"
                                :class="selectedPaymentMethod === 'QRIS' ? 'border-emerald-600 bg-emerald-600 text-white font-bold' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'">
                            QRIS
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'TRANSFER'"
                                class="py-2 rounded-xl border text-center transition shadow-xs"
                                :class="selectedPaymentMethod === 'TRANSFER' ? 'border-emerald-600 bg-emerald-600 text-white font-bold' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'">
                            Transfer
                        </button>
                        <button type="button"
                                @click="selectedPaymentMethod = 'MULTI'"
                                class="py-2 rounded-xl border text-center transition shadow-xs"
                                :class="selectedPaymentMethod === 'MULTI' ? 'border-emerald-600 bg-emerald-600 text-white font-bold' : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'">
                            Kombinasi
                        </button>
                    </div>

                    <!-- Mode CASH: Input Uang Diterima -->
                    <div x-show="selectedPaymentMethod === 'CASH'" class="space-y-2.5">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-semibold text-slate-700">Nominal Tunai Diterima</label>
                            <span class="text-[11px] text-slate-500">Pas: <strong>Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span></strong></span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedCashGiven"
                                   @input="setFormattedCashGiven($event.target.value)"
                                   placeholder="0"
                                   class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm font-bold text-slate-800 font-mono tabular-nums focus:outline-none focus:ring-2 focus:ring-emerald-400/40 focus:border-emerald-400">
                        </div>
                        
                        <!-- Quick Cash Buttons -->
                        <div class="grid grid-cols-4 gap-1.5 text-xs">
                            <button type="button" @click="cashGiven = finalTotal" class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold border border-slate-200 transition">Uang Pas</button>
                            <button type="button" @click="cashGiven = 20000" class="py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">20.000</button>
                            <button type="button" @click="cashGiven = 50000" class="py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">50.000</button>
                            <button type="button" @click="cashGiven = 100000" class="py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">100.000</button>
                        </div>

                        <!-- Kembalian Tunai -->
                        <div class="flex justify-between items-center text-xs text-emerald-800 bg-emerald-50/80 p-3 rounded-xl border border-emerald-200 font-semibold">
                            <span>Kembalian:</span>
                            <span class="font-bold text-emerald-700 font-mono tabular-nums text-sm">
                                Rp<span x-text="Number(changeDue).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Mode QRIS: Tampilan QR Code Dinamis Otomatis Nominal -->
                    <div x-show="selectedPaymentMethod === 'QRIS'" class="space-y-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200 text-center">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="bg-rose-600 text-white px-2 py-0.5 rounded text-[9px] font-bold font-mono">QRIS</span>
                                <span class="bg-indigo-600 text-white px-1.5 py-0.5 rounded text-[8px] font-bold font-mono">GPN</span>
                            </div>
                            <span class="text-[9px] font-semibold font-mono uppercase text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">
                                🟢 Nominal Terkunci Otomatis
                            </span>
                        </div>

                        <!-- Merchant & Amount Info -->
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 font-mono block">
                                {{ $outlet->qris_merchant_name ?? $outlet->name ?? 'SOTO LAMONGAN LESTARI' }}
                            </span>
                            <div class="text-2xl sm:text-3xl font-bold text-slate-800 font-mono tracking-tight my-1">
                                Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 block">
                                Pembeli tinggal scan dari layar tablet kasir ini.
                            </span>
                        </div>

                        <!-- QR Code Canvas Display -->
                        <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-xs inline-block mx-auto relative">
                            <canvas id="qris-pos-canvas" class="mx-auto block"></canvas>
                            
                            <!-- Timer & Refresh -->
                            <div class="mt-2 pt-1 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-slate-400">
                                <span>Berlaku: <strong class="text-rose-500" x-text="qrisTimerFormatted"></strong></span>
                                <button type="button" @click="generateQris()" class="text-emerald-700 font-semibold hover:underline">
                                    🔄 Refresh QR
                                </button>
                            </div>
                        </div>

                        <!-- Action Bar Khusus Tablet Kasir: Sodorkan Layar Penuh -->
                        <div class="flex items-center justify-center gap-2 pt-1">
                            <button type="button" 
                                    @click="openQrisFullScreen()" 
                                    class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                                <span>📱 Perbesar Layar (Sodorkan Tablet ke Pembeli)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mode MULTI: Kombinasi Pembayaran Tunai + Non Tunai (Split Bill) -->
                    <div x-show="selectedPaymentMethod === 'MULTI'" class="space-y-3 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Kombinasi Pembayaran (Split Bill)</span>
                        
                        <!-- Porsi Tunai -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nominal Tunai</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-slate-400 font-mono text-xs font-bold">Rp</span>
                                <input type="text" 
                                       inputmode="numeric"
                                       :value="formattedMultiCash"
                                       @input="setFormattedMultiCash($event.target.value)"
                                       placeholder="0"
                                       class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-1.5 text-xs font-bold font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-emerald-400">
                            </div>
                        </div>

                        <!-- Porsi Non-Tunai -->
                        <div class="space-y-1">
                            <div class="flex justify-between items-center">
                                <label class="block text-[11px] font-semibold text-slate-700">Nominal Non-Tunai</label>
                                <div class="flex gap-2.5 text-[10px] font-semibold text-slate-600">
                                    <label class="cursor-pointer flex items-center gap-1">
                                        <input type="radio" value="QRIS" x-model="multiNonCashMethod" class="text-emerald-600"> QRIS
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-1">
                                        <input type="radio" value="TRANSFER" x-model="multiNonCashMethod" class="text-emerald-600"> Transfer
                                    </label>
                                </div>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-slate-400 font-mono text-xs font-bold">Rp</span>
                                <input type="text" 
                                       inputmode="numeric"
                                       :value="formattedMultiNonCash"
                                       @input="setFormattedMultiNonCash($event.target.value)"
                                       placeholder="0"
                                       class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-1.5 text-xs font-bold font-mono tabular-nums focus:outline-none focus:ring-1 focus:ring-emerald-400">
                            </div>
                            <template x-if="multiNonCashMethod === 'QRIS' && multiNonCashAmount > 0">
                                <button type="button" 
                                        @click="generateQris()" 
                                        class="mt-1 text-[10px] text-emerald-700 font-semibold hover:underline block font-mono">
                                    📱 Tampilkan QRIS Non-Tunai (Rp<span x-text="Number(multiNonCashAmount).toLocaleString('id-ID')"></span>)
                                </button>
                            </template>
                        </div>

                        <!-- Ringkasan Split -->
                        <div class="pt-2 border-t border-slate-200 text-xs space-y-1">
                            <div class="flex justify-between text-slate-500">
                                <span>Total Terkumpul:</span>
                                <span class="font-bold text-slate-800 font-mono tabular-nums">Rp<span x-text="Number(totalMultiPaid).toLocaleString('id-ID')"></span></span>
                            </div>
                            <template x-if="remainingMultiDue > 0">
                                <div class="flex justify-between text-rose-600 font-bold">
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
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama / Catatan Pelanggan</label>
                        <input type="text" x-model="customerName" placeholder="Contoh: Bpk Ahmad / Meja 04" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                    </div>
                </div>

                <!-- Confirm Action Button -->
                <div class="p-4 border-t border-slate-100 bg-slate-50 shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                    <button type="button"
                            @click="processCheckout()"
                            :disabled="isProcessingCheckout || (selectedPaymentMethod === 'CASH' && cashGiven < finalTotal) || (selectedPaymentMethod === 'MULTI' && totalMultiPaid < finalTotal)"
                            class="w-full h-11 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs tracking-wider uppercase transition shadow-xs flex items-center justify-center gap-1.5 active:translate-y-px bg-emerald-600 hover:bg-emerald-700">
                        <span x-text="isProcessingCheckout ? 'Menyimpan Transaksi...' : (selectedPaymentMethod === 'QRIS' ? '✓ Selesaikan Pembayaran QRIS (Lunas)' : 'Selesaikan Pembayaran')"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Fullscreen QRIS Modal khusus Tablet Kasir (Sodorkan ke Pembeli) -->
        <div x-show="qrisFullScreenModalOpen" 
             class="fixed inset-0 bg-slate-900/75 z-50 flex items-center justify-center p-3 sm:p-4 animate-in fade-in"
             x-cloak>
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 text-center space-y-4 my-auto"
                 @click.outside="qrisFullScreenModalOpen = false">
                
                <!-- Top Banner QRIS -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="bg-rose-600 text-white px-2 py-0.5 rounded text-xs font-bold tracking-widest font-mono">QRIS</span>
                        <span class="bg-indigo-600 text-white px-1.5 py-0.5 rounded text-[10px] font-bold font-mono">GPN</span>
                    </div>
                    <button type="button" 
                            @click="qrisFullScreenModalOpen = false" 
                            class="text-slate-400 hover:text-rose-600 font-semibold text-xs p-1">
                        ✕ Tutup (ESC)
                    </button>
                </div>

                <!-- Outlet Name & Big Amount -->
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 font-mono block">
                        {{ $outlet->qris_merchant_name ?? $outlet->name ?? 'SOTO LAMONGAN LESTARI' }}
                    </span>
                    <div class="text-3xl sm:text-4xl font-bold text-slate-800 font-mono tracking-tight my-1">
                        Rp<span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                    </div>
                    <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[10px] font-semibold px-2.5 py-0.5 rounded-full">
                        🟢 Nominal Otomatis Terkunci
                    </span>
                </div>

                <!-- Big Canvas QRIS -->
                <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-xs inline-block mx-auto">
                    <canvas id="qris-fullscreen-canvas" class="mx-auto block"></canvas>
                </div>

                <p class="text-xs text-slate-500 leading-snug">
                    Silakan scan dengan <strong>BCA, Mandiri, BRImo, GoPay, ShopeePay, Dana, OVO</strong>.<br>
                    Nominal tagihan sudah terisi otomatis.
                </p>

                <!-- Bottom Action -->
                <div class="pt-2 flex flex-col gap-2">
                    <button type="button" 
                            @click="qrisFullScreenModalOpen = false; processCheckout();"
                            :disabled="isProcessingCheckout"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm uppercase tracking-wider rounded-xl shadow-xs active:translate-y-px transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="isProcessingCheckout ? 'Menyimpan...' : 'Pelanggan Sudah Bayar (Lunas)'"></span>
                    </button>

                    <button type="button" 
                            @click="qrisFullScreenModalOpen = false" 
                            class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-xl transition">
                        Kembali ke Kasir
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. Success Modal -->
        <div x-show="successModalOpen"
             @click.self="successModalOpen = false"
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-slate-800 rounded-3xl max-w-sm sm:max-w-md w-full border border-slate-200/90 shadow-xl my-auto max-h-[92vh] flex flex-col overflow-hidden p-5 text-center animate-in fade-in duration-150"
                 @click.stop>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-2.5 font-bold text-lg shrink-0">
                    ✓
                </div>

                <h3 class="text-base font-bold text-slate-800">Pembayaran Berhasil</h3>
                <p class="text-xs text-slate-500 mt-0.5">Transaksi lunas, stok bahan baku resep otomatis terpotong.</p>

                <!-- Receipt Summary -->
                <div class="bg-slate-50 rounded-2xl p-3.5 my-3 text-left border border-slate-200 text-xs space-y-1.5 overflow-y-auto max-h-48">
                    <div class="flex justify-between items-center text-slate-500">
                        <span>No. Struk</span>
                        <span class="font-mono tabular-nums font-bold text-slate-800" x-text="completedOrder?.order_number || '-'"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Metode</span>
                        <span class="font-semibold text-slate-800" x-text="completedOrder?.payment?.method || (completedOrder?.payments ? 'KOMBINASI' : 'CASH')"></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500 pt-1.5 border-t border-slate-200">
                        <span class="font-bold text-slate-800">Total</span>
                        <span class="font-bold text-emerald-700 font-mono tabular-nums text-sm">Rp<span x-text="Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <template x-if="completedOrder?.changeDue > 0">
                        <div class="flex justify-between items-center text-emerald-700 font-semibold">
                            <span>Kembalian</span>
                            <span class="font-mono tabular-nums">Rp<span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span></span>
                        </div>
                    </template>
                </div>

                <!-- Auto-Print Toggle Checkbox -->
                <div class="p-2.5 bg-emerald-50/70 rounded-2xl border border-emerald-200/80 mb-3 text-left">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" 
                               :checked="autoPrintReceipt" 
                               @change="toggleAutoPrint()" 
                               class="rounded-md text-emerald-600 focus:ring-emerald-400 w-4 h-4">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Auto-Print Struk Tiap Transaksi</span>
                            <span class="text-[10px] text-slate-500">Struk otomatis tercetak saat pembayaran selesai tanpa klik tombol.</span>
                        </div>
                    </label>
                </div>

                <div class="space-y-2 shrink-0">
                    <button type="button"
                            @click="window.thermalPrinter && window.thermalPrinter.printReceipt(completedOrder)"
                            class="w-full h-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center justify-center gap-1.5 active:translate-y-px shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Struk Thermal Pelanggan (58mm)</span>
                    </button>
                    
                    <button type="button"
                            @click="printKitchenTicket()"
                            class="w-full h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition flex items-center justify-center">
                        Cetak Tiket Dapur (KOT)
                    </button>

                    <button type="button"
                            @click="successModalOpen = false"
                            class="w-full h-9 rounded-xl text-slate-500 hover:text-slate-800 font-semibold text-xs transition flex items-center justify-center">
                        Transaksi Baru (Selesai)
                    </button>
                </div>
            </div>
        </div>

        <!-- 8. Open Bills Modal (Daftar Tagihan Gantung / Hold Orders) -->
        <div x-show="openBillsModalOpen" 
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white text-slate-800 rounded-3xl max-w-lg w-full border border-slate-200 shadow-xl my-auto max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in duration-150"
                 @click.outside="openBillsModalOpen = false">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/70">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Daftar Open Bill Aktif</h3>
                        <p class="text-[11px] text-slate-500">Pesanan makan di tempat yang belum dilunasi.</p>
                    </div>
                    <button type="button" @click="openBillsModalOpen = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 space-y-2">
                    <template x-if="openBills.length === 0">
                        <div class="p-6 text-center text-slate-400 text-xs">
                            Tidak ada Open Bill yang sedang aktif.
                        </div>
                    </template>

                    <template x-for="bill in openBills" :key="bill.id">
                        <div class="p-3 rounded-2xl border border-slate-200/90 hover:border-emerald-300 bg-white space-y-1.5 transition shadow-xs">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-semibold text-slate-800 block" x-text="bill.customer_name || 'Pelanggan'"></span>
                                    <span class="text-[10px] font-mono text-slate-400" x-text="`No: ${bill.order_number}`"></span>
                                </div>
                                <span class="text-xs font-bold text-emerald-700 font-mono tabular-nums">
                                    Rp<span x-text="Number(bill.final_amount).toLocaleString('id-ID')"></span>
                                </span>
                            </div>

                            <div class="text-[11px] text-slate-600 line-clamp-1">
                                <span x-text="`${bill.items?.length || 0} menu:`"></span>
                                <template x-for="itm in (bill.items || [])" :key="itm.id">
                                    <span class="text-slate-500 mr-1" x-text="`${itm.name || itm.product?.name} (${itm.quantity})`"></span>
                                </template>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                <span class="text-[10px] text-slate-400 font-mono" x-text="new Date(bill.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
                                <button type="button" 
                                        @click="resumeOpenBill(bill)"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
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
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full p-5 border border-slate-200 shadow-xl animate-in fade-in duration-150"
                 @click.outside="startShiftModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Buka Shift Kasir Baru</h3>
                    <button type="button" @click="startShiftModalOpen = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3.5 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Modal Awal Laci Kasir (Rp) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedStartCash"
                                   @input="setFormattedStartCash($event.target.value)"
                                   placeholder="Contoh: 200.000" 
                                   class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-bold font-mono tabular-nums text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kasir (Opsional)</label>
                        <input type="text" x-model="startCashNotes" placeholder="Contoh: Shift Pagi Kasir 1" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="startShiftModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Batal</button>
                        <button type="button" @click="startShift()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">Buka Shift Sekarang</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10. Kas Keluar / Petty Cash Modal -->
        <div x-show="pettyCashModalOpen" 
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full p-5 border border-slate-200 shadow-xl animate-in fade-in duration-150"
                 @click.outside="pettyCashModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pencatatan Kas Keluar (Petty Cash)</h3>
                    <button type="button" @click="pettyCashModalOpen = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3.5 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Kas Keluar (Rp) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedPettyCash"
                                   @input="setFormattedPettyCash($event.target.value)"
                                   placeholder="Contoh: 25.000" 
                                   class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-bold font-mono tabular-nums text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Pengeluaran <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="pettyCashReason" placeholder="Contoh: Beli Es Batu kristal, Gas Elpiji 3kg" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="pettyCashModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Batal</button>
                        <button type="button" @click="recordPettyCash()" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs">Catat Kas Keluar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 11. Tutup Shift Kasir (Blind Close Modal) -->
        <div x-show="closeShiftModalOpen" 
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full p-5 border border-slate-200 shadow-xl animate-in fade-in duration-150"
                 @click.outside="closeShiftModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Tutup Shift Kasir (Blind Close)</h3>
                        <p class="text-[10px] text-slate-500">Hitung uang fisik di laci sebelum sistem menampilkan selisih.</p>
                    </div>
                    <button type="button" @click="closeShiftModalOpen = false" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3.5 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Uang Fisik Kasir di Laci (Rp) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-mono text-xs font-bold">Rp</span>
                            <input type="text" 
                                   inputmode="numeric"
                                   :value="formattedActualCash"
                                   @input="setFormattedActualCash($event.target.value)"
                                   placeholder="Contoh: 1.450.000" 
                                   class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-bold font-mono tabular-nums text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tutup Shift (Opsional)</label>
                        <input type="text" x-model="closeShiftNotes" placeholder="Contoh: Selesai shift sore kondisi aman" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-400">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="closeShiftModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Batal</button>
                        <button type="button" @click="closeShift()" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs">Hitung & Tutup Shift</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 12. Ringkasan Tutup Shift (Shift Summary Report Modal) -->
        <div x-show="shiftSummaryModalOpen" 
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full p-5 border border-slate-200 shadow-xl animate-in fade-in duration-150 text-center"
                 @click.outside="shiftSummaryModalOpen = false">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center mx-auto mb-2.5 font-bold text-base">
                    ✓
                </div>
                <h3 class="text-sm font-bold text-slate-800">Shift Kasir Ditutup</h3>
                <p class="text-[11px] text-slate-500">Laporan audit selisih kas telah disimpan di pembukuan.</p>

                <div class="bg-slate-50 rounded-2xl p-3.5 my-3.5 text-left border border-slate-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Modal Awal:</span>
                        <span class="font-mono tabular-nums font-semibold text-slate-800">Rp<span x-text="Number(shiftReport?.start_cash || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Penjualan Tunai:</span>
                        <span class="font-mono tabular-nums font-semibold text-emerald-700">+Rp<span x-text="Number(shiftReport?.cash_sales || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kas Keluar (Petty Cash):</span>
                        <span class="font-mono tabular-nums font-semibold text-rose-600">-Rp<span x-text="Number(shiftReport?.petty_cash_out || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-1.5 font-bold text-slate-800">
                        <span>Ekspektasi Kas Laci:</span>
                        <span class="font-mono tabular-nums">Rp<span x-text="Number(shiftReport?.expected_cash || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Uang Fisik Dihitung:</span>
                        <span class="font-mono tabular-nums font-bold text-slate-800">Rp<span x-text="Number(shiftReport?.actual_cash || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between border-t border-dotted border-slate-300 pt-1.5 font-bold"
                         :class="(shiftReport?.difference || 0) < 0 ? 'text-rose-600' : ((shiftReport?.difference || 0) > 0 ? 'text-emerald-700' : 'text-slate-800')">
                        <span>Selisih (Short/Over):</span>
                        <span class="font-mono tabular-nums">
                            <span x-text="(shiftReport?.difference || 0) > 0 ? '+Rp' : 'Rp'"></span>
                            <span x-text="Number(shiftReport?.difference || 0).toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                <button type="button" @click="shiftSummaryModalOpen = false" class="w-full h-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                    Tutup Laporan
                </button>
            </div>
        </div>
    </div> <!-- /#pos-app-ui (Diisolasi agar tidak memakan ruang cetak thermal 58mm) -->

    <!-- ========================================================================= -->
    <!-- THERMAL PRINT CONTAINERS (ISOLASI PENUH DI LUAR LAYOUT UTAMA UNTUK 58MM)   -->
    <!-- ========================================================================= -->

    <!-- 13. 58mm/80mm Thermal Receipt Container (Cetak Struk Transaksi Pelanggan) -->
    <div id="printable-receipt" class="print-only text-black font-mono select-none" style="width: 48mm; max-width: 48mm; margin: 0 auto; page-break-inside: avoid; break-inside: avoid;">
        <div class="text-center pb-2 border-b border-dashed border-black">
            <h2 class="text-xs font-bold tracking-wider uppercase text-black">SOTO LAMONGAN LESTARI</h2>
            <p class="text-[10px] mt-0.5 font-semibold text-black">{{ $outlet->name ?? 'Cabang Utama' }}</p>
            <p class="text-[10px] font-semibold text-black">Telp: 0852-1325-4219</p>
        </div>

        <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
            <div class="flex justify-between">
                <span>No: <strong class="font-bold text-black" x-text="completedOrder?.order_number"></strong></span>
                <span class="font-semibold text-black" x-text="new Date(completedOrder?.created_at || Date.now()).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
            </div>
            <div class="flex justify-between">
                <span>Tgl: <span class="font-semibold text-black" x-text="new Date(completedOrder?.created_at || Date.now()).toLocaleDateString('id-ID')"></span></span>
                <span class="font-bold uppercase text-black" x-text="orderType"></span>
            </div>
            <div class="flex justify-between" x-show="completedOrder?.customer_name">
                <span>Plg: <span class="font-semibold text-black" x-text="completedOrder?.customer_name"></span></span>
            </div>
        </div>

        <!-- Items -->
        <div class="py-1.5 border-b border-dashed border-black space-y-1">
            <template x-for="item in (completedOrder?.items || [])" :key="item.name">
                <div>
                    <div class="font-bold text-[11px] text-black" x-text="item.name"></div>
                    <div class="flex justify-between text-[10px]">
                        <span class="font-semibold text-black" x-text="`${item.quantity} x ${Number(item.unit_price).toLocaleString('id-ID')}`"></span>
                        <span class="font-bold text-black" x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                    </div>
                    <template x-if="item.notes">
                        <div class="text-[9px] font-semibold text-black italic" x-text="`* ${item.notes}`"></div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Totals -->
        <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
            <div class="flex justify-between">
                <span class="font-semibold text-black">Subtotal</span>
                <span class="font-bold text-black" x-text="Number(completedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
            </div>
            <div class="flex justify-between" x-show="completedOrder?.discount_amount > 0">
                <span class="font-semibold text-black">Diskon</span>
                <span class="font-bold text-black" x-text="`-${Number(completedOrder?.discount_amount || 0).toLocaleString('id-ID')}`"></span>
            </div>
            <div class="flex justify-between" x-show="Number(completedOrder?.tax_amount || 0) > 0">
                <span class="font-semibold text-black">Pajak</span>
                <span class="font-bold text-black" x-text="Number(completedOrder?.tax_amount || 0).toLocaleString('id-ID')"></span>
            </div>
            <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-black">
                <span class="text-black">TOTAL</span>
                <span class="text-black" x-text="`Rp${Number(completedOrder?.final_amount || 0).toLocaleString('id-ID')}`"></span>
            </div>
        </div>

        <!-- Payment info -->
        <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
            <div class="flex justify-between">
                <span class="font-semibold text-black">Metode</span>
                <span class="font-bold uppercase text-black" x-text="completedOrder?.payment?.method || (completedOrder?.payments ? 'KOMBINASI' : 'CASH')"></span>
            </div>
            <template x-if="completedOrder?.changeDue > 0">
                <div class="flex justify-between font-bold text-black">
                    <span>Kembalian</span>
                    <span x-text="Number(completedOrder?.changeDue || 0).toLocaleString('id-ID')"></span>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="text-center pt-2 text-[10px] space-y-0.5 font-semibold text-black">
            <p class="font-bold">Rasa Mantap, Harga Bersahabat</p>
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p class="text-[9px] pt-1">SLL Smart POS Cloud</p>
        </div>
    </div>

    <!-- 14. Kitchen Order Ticket (KOT) Thermal Container (Khusus Dapur & Bar, Tanpa Harga) -->
    <div id="printable-kot" class="print-only text-black font-mono select-none" style="width: 48mm; max-width: 48mm; margin: 0 auto; page-break-inside: avoid; break-inside: avoid;">
        <div class="text-center pb-2 border-b-2 border-black">
            <h2 class="text-sm font-black tracking-wider uppercase text-black">TIKET DAPUR (KOT)</h2>
            <p class="text-xs font-bold text-black">{{ $outlet->name ?? 'Cabang Utama' }}</p>
        </div>

        <div class="py-2 border-b border-black text-xs space-y-0.5">
            <div class="flex justify-between font-bold text-black">
                <span>TIPE:</span>
                <span class="uppercase text-sm" x-text="orderType"></span>
            </div>
            <div class="flex justify-between text-black">
                <span class="font-bold">MEJA/PLG:</span>
                <span class="font-black text-sm" x-text="selectedTableName || customerName || 'Dine-In'"></span>
            </div>
            <div class="flex justify-between text-[10px] font-semibold text-black">
                <span>WAKTU:</span>
                <span x-text="new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></span>
            </div>
        </div>

        <!-- List Menu yang Dimasak -->
        <div class="py-2 border-b-2 border-black space-y-2">
            <template x-for="item in (cart.length > 0 ? cart : (completedOrder?.items || []))" :key="item.name">
                <div class="pb-1 border-b border-dotted border-black">
                    <div class="flex justify-between items-start text-xs font-black text-black">
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

        <div class="text-center pt-2 text-[10px] font-black text-black">
            *** SEGERA DISAJIKAN PANAS ***
        </div>
    </div>
</body>
</html>
