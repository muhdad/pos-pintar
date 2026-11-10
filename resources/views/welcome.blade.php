<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS Pintar — Smart Cloud POS & AI Business Consultant</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-gradient-to-br from-[#e0f0fe] via-[#eaf4fe] to-[#f4f9ff] text-slate-800 min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-40 bg-white/70 backdrop-blur-md border-b border-blue-100/80 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-extrabold text-xl shadow-md shadow-blue-500/25 group-hover:scale-105 transition">
                    P
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-base text-slate-900 tracking-tight">POS Pintar</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full border border-blue-200">AI Powered</span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium">Smart Cloud & Offline-First POS</p>
                </div>
            </a>

            <!-- Nav Links & Actions -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Backoffice</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-500/25 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Buka Terminal Kasir</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-500/25 transition flex items-center gap-1">
                            <span>Mulai Sekarang</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="pt-12 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto">
                <!-- Pill Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/80 border border-blue-200 text-blue-700 text-xs font-semibold mb-6 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    <span>TALL Stack + Offline-First (Dexie.js) + AI Co-Pilot</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15] mb-6">
                    Aplikasi Kasir <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">0ms Latency</span> dengan Konsultan AI Pintar
                </h1>

                <!-- Subheadline -->
                <p class="text-base sm:text-lg text-slate-600 mb-8 leading-relaxed">
                    Dirancang khusus untuk UMKM F&B dan Retail Indonesia. Kasir tetap jalan 100% saat offline, resep bahan baku (BOM) terpotong otomatis, dan dapatkan insight bisnis harian langsung dari AI.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-10">
                    <a href="{{ route('pos.index') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm tracking-wide shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Coba Terminal Kasir (0ms)</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-sm border border-blue-200/80 shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Buka Dashboard Backoffice</span>
                    </a>
                </div>

                <!-- 1-Click Demo Login Helper -->
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/80 border border-blue-200/80 text-xs text-slate-600 shadow-sm">
                    <span class="font-bold text-slate-800">Akun Demo Siap Pakai:</span>
                    <span class="text-slate-500">Owner: <code class="text-blue-600 font-semibold">owner@kopisenja.com</code> (PIN 0001)</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-500">Kasir: <code class="text-blue-600 font-semibold">kasir@kopisenja.com</code> (PIN 1024)</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-500">Pass: <code class="text-slate-700 font-semibold">password</code></span>
                </div>
            </div>

            <!-- Interactive UI Preview Showcase Mockup -->
            <div class="mt-14 max-w-5xl mx-auto rounded-3xl bg-white/90 backdrop-blur-xl border border-blue-200/90 p-4 sm:p-6 shadow-[0_20px_50px_rgba(37,99,235,0.08)]">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 text-xs font-semibold text-slate-500">POS Pintar • Terminal Kasir & AI Insight Live Preview</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                            🟢 Online & Auto-Sync Ready
                        </span>
                    </div>
                </div>

                <!-- Split Preview: Cashier POS + AI Consultant -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Left: Cashier Preview (7 Cols) -->
                    <div class="lg:col-span-7 bg-slate-50/80 rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Katalog Menu & Keranjang (Alpine.js)</span>
                            <span class="text-[11px] font-medium text-blue-600">Cabang Kemang</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-white p-2.5 rounded-xl border border-blue-100 shadow-2xs">
                                <div class="text-[11px] font-bold text-slate-800">Kopi Susu Gula Aren</div>
                                <div class="text-[10px] text-slate-400">Resep: 18g Kopi, 120ml Susu</div>
                                <div class="text-xs font-bold text-blue-600 mt-1">Rp22.000</div>
                            </div>
                            <div class="bg-white p-2.5 rounded-xl border border-blue-100 shadow-2xs">
                                <div class="text-[11px] font-bold text-slate-800">Caffe Latte</div>
                                <div class="text-[10px] text-slate-400">Resep: 18g Kopi, 180ml Susu</div>
                                <div class="text-xs font-bold text-blue-600 mt-1">Rp25.000</div>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200 text-xs">
                            <div class="flex justify-between font-bold text-slate-800 pb-1.5 border-b border-slate-100">
                                <span>Total Bayar (2 Menu)</span>
                                <span class="text-blue-600">Rp47.000</span>
                            </div>
                            <div class="text-[10px] text-emerald-600 font-medium mt-1">
                                ✓ Stok lokal Dexie.js terpotong seketika (36g Kopi, 300ml Susu)
                            </div>
                        </div>
                    </div>

                    <!-- Right: AI Executive Insight (5 Cols) -->
                    <div class="lg:col-span-5 bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl p-4 flex flex-col justify-between shadow-md">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-200">AI Business Consultant</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white">Skor 85/100</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug mb-2">
                                Rekomendasi Bundling & Evaluasi Stok
                            </h3>
                            <p class="text-xs text-blue-100 leading-relaxed mb-3">
                                "Margin kotor hari ini stabil di 55.9%. Padukan <strong class="text-white">Kopi Susu Gula Aren</strong> dengan <strong class="text-white">Americano</strong> untuk menguras stok bahan baku yang menumpuk."
                            </p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-2.5 backdrop-blur-xs text-[11px] flex items-center justify-between">
                            <span class="text-blue-200">Deteksi Anomali Kasir:</span>
                            <span class="font-bold text-emerald-300">Aman (0 Void)</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid Section -->
        <section class="py-16 bg-white/80 border-t border-b border-blue-100/70">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
                        Dirancang dengan Standar Aplikasi Kelas Dunia
                    </h2>
                    <p class="text-sm text-slate-600">
                        Pemisahan tegas antara operasional kasir berkecepatan tinggi dengan backoffice analisis bisnis yang mendalam.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="bg-gradient-to-br from-[#f8fbff] to-[#edf5ff] rounded-3xl p-6 border border-blue-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-xl mb-4 shadow-sm shadow-blue-500/25">
                            ⚡
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Kasir Offline-First (0ms Latency)</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Menggunakan Alpine.js dan IndexedDB (Dexie.js). Klik menu, ubah kuantitas, dan hitung pajak instan tanpa menunggu request server. Tetap dapat bertransaksi saat internet mati total.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-gradient-to-br from-[#f8fbff] to-[#edf5ff] rounded-3xl p-6 border border-blue-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl mb-4 shadow-sm shadow-indigo-500/25">
                            ☕
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Resep Bahan Baku (BOM) & HPP</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Satu cangkir kopi terjual otomatis memotong 18g biji kopi, 120ml susu, dan 1 cup plastik. Nilai HPP (Moving Average COGS) tercatat akurat untuk analisis laba-rugi riil.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-gradient-to-br from-[#f8fbff] to-[#edf5ff] rounded-3xl p-6 border border-blue-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl mb-4 shadow-sm shadow-emerald-500/25">
                            🤖
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">AI Business Consultant</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Agregasi metrik cerdas menghemat token LLM. Setiap malam sistem merangkum omzet, mendeteksi menu dead-stock, dan memberikan rekomendasi promo taktis untuk esok hari.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-gradient-to-br from-[#f8fbff] to-[#edf5ff] rounded-3xl p-6 border border-blue-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-bold text-xl mb-4 shadow-sm shadow-purple-500/25">
                            🔒
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Dual-Mode & 4-Digit PIN Lock</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Tablet di meja kasir dapat dipakai bersama antar-shift karyawan dengan cepat via 4-digit PIN lock tanpa logout email akun Owner. Menu keuangan Backoffice otomatis terlindungi.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-gradient-to-br from-[#f8fbff] to-[#edf5ff] rounded-3xl p-6 border border-blue-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-bold text-xl mb-4 shadow-sm shadow-rose-500/25">
                            🖨️
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Printer Thermal & QRIS</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Terintegrasi dengan Web Bluetooth API untuk mencetak struk langsung ke printer thermal ESC/POS 58mm & 80mm standar tanpa instal driver tambahan.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-gradient-to-br from-[#f8fbff] to-[#edf5ff] rounded-3xl p-6 border border-blue-100 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-bold text-xl mb-4 shadow-sm shadow-amber-500/25">
                            📊
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Snapshot Integritas Finansial</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Setiap baris transaksi menyimpan snapshot harga jual dan modal (HPP) saat transaksi dibuat. Laporan laba kotor di masa lalu tidak akan pernah terdistorsi saat harga barang naik.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-blue-100 py-8 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">P</span>
                <span class="font-bold text-slate-800">POS Pintar</span>
                <span>— Smart Cloud POS & AI Consultant untuk UMKM Indonesia</span>
            </div>
            <div>
                <span>© {{ date('Y') }} POS Pintar. Dibuat dengan arsitektur Laravel Monolith & TALL Stack.</span>
            </div>
        </div>
    </footer>

</body>
</html>
