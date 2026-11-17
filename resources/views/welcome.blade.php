<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLL - Soto Lamongan Lestari • Smart Cloud POS & AI Resto Consultant</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-gradient-to-br from-[#1c0404] via-[#2a0707] to-[#120202] text-slate-100 min-h-screen flex flex-col selection:bg-amber-500 selection:text-white">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-40 bg-[#1c0404]/90 backdrop-blur-md border-b border-amber-500/20 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-700 text-amber-950 flex items-center justify-center font-black text-2xl shadow-lg shadow-orange-600/30 border border-amber-300/40 group-hover:scale-105 transition transform">
                    <span class="tracking-tighter drop-shadow-xs font-black">SLL</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-black text-base text-white tracking-tight">SOTO LAMONGAN LESTARI</span>
                        <span class="px-2 py-0.5 text-[9px] font-black bg-gradient-to-r from-amber-500 to-orange-500 text-amber-950 rounded-full tracking-wider uppercase">Resto POS</span>
                    </div>
                    <p class="text-[11px] text-amber-300/80 font-medium">Pusat Kuliner Lamongan • Pesanan: +62 852-1325-4219</p>
                </div>
            </a>

            <!-- Nav Links & Actions -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-xs font-bold text-amber-200 bg-white/10 hover:bg-white/15 border border-amber-500/30 rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Backoffice</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="px-4 py-2 text-xs font-black text-amber-950 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-400 hover:to-orange-500 rounded-xl shadow-md shadow-orange-600/30 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Layar Kasir (POS)</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-amber-200/80 hover:text-white rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-black text-amber-950 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-400 hover:to-orange-500 rounded-xl shadow-md shadow-orange-600/30 transition flex items-center gap-1">
                            <span>Mulai Sekarang</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="pt-14 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative">
            <!-- Ambient Culinary Glow -->
            <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-orange-600/15 blur-3xl pointer-events-none rounded-full"></div>

            <div class="text-center max-w-3xl mx-auto relative z-10">
                <!-- Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-bold mb-6 shadow-xs backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span>SOTO LAMONGAN LESTARI • SMART CLOUD POS & AI RESTO</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15] mb-6">
                    Aplikasi Kasir <span class="bg-gradient-to-r from-amber-400 via-orange-500 to-amber-300 bg-clip-text text-transparent">0ms Latency</span> dengan Konsultan AI Resto
                </h1>

                <!-- Subheadline -->
                <p class="text-base sm:text-lg text-amber-100/80 mb-8 leading-relaxed">
                    Didesain khusus untuk operasional Soto Lamongan Lestari. Kasir tetap bertransaksi 100% cepat saat internet offline, stok resep kuah kaldu, daging, & koya terpotong otomatis, dan laporan laba kotor dihitung akurat.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 mb-10">
                    <a href="{{ route('pos.index') }}" class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-400 hover:to-orange-500 text-amber-950 font-black text-sm tracking-wide shadow-xl shadow-orange-600/30 transition flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Buka Layar Kasir (0ms Offline-First)</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-[#2b0808] hover:bg-[#380b0b] text-white font-bold text-sm border border-amber-500/30 shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Dashboard Backoffice Resto</span>
                    </a>
                </div>

                <!-- 1-Click Demo Login Helper -->
                <div class="inline-flex flex-wrap items-center justify-center gap-2 sm:gap-3 px-5 py-2.5 rounded-2xl bg-black/40 border border-amber-500/30 text-xs text-amber-200/80 shadow-md backdrop-blur-sm">
                    <span class="font-bold text-white">Akun Siap Pakai:</span>
                    <span>Admin: <code class="text-amber-400 font-mono font-bold">admin@pospintar.com</code> (PIN 1234)</span>
                    <span class="text-amber-500/40 hidden sm:inline">|</span>
                    <span>Kasir: <code class="text-amber-400 font-mono font-bold">kasir@sll-lestari.com</code> (PIN 1234)</span>
                    <span class="text-amber-500/40 hidden sm:inline">|</span>
                    <span>Pass: <code class="text-amber-300 font-mono font-bold">admin123</code></span>
                </div>
            </div>

            <!-- Interactive UI Preview Showcase Mockup -->
            <div class="mt-14 max-w-5xl mx-auto rounded-3xl bg-[#230606]/90 backdrop-blur-xl border border-amber-500/30 p-4 sm:p-6 shadow-[0_20px_60px_rgba(0,0,0,0.6)] relative z-10">
                <div class="flex items-center justify-between pb-4 border-b border-amber-500/20 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="ml-2 text-xs font-bold text-amber-200/90">Soto Lamongan Lestari • Terminal Kasir & AI Resto Live Preview</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            🟢 Online & Auto-Sync Ready
                        </span>
                    </div>
                </div>

                <!-- Split Preview: Cashier POS + AI Consultant -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Left: Cashier Preview (7 Cols) -->
                    <div class="lg:col-span-7 bg-black/40 rounded-2xl p-4 border border-amber-500/20">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-extrabold text-amber-300 uppercase tracking-wider">Katalog Menu SLL (Alpine.js + Dexie)</span>
                            <span class="text-[11px] font-semibold text-amber-200/70">Cabang Utama • Order: 0852-1325-4219</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-[#2d0707] p-3 rounded-xl border border-amber-500/30 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">🍲</span>
                                    <span class="text-[10px] font-bold text-amber-400 bg-black/40 px-1.5 py-0.5 rounded">Soto</span>
                                </div>
                                <div class="text-xs font-bold text-white mt-1">Soto Ayam Campur Nasi</div>
                                <div class="text-[10px] text-amber-200/60">Ayam suwir, tauge, soun, koya</div>
                                <div class="text-xs font-black text-amber-400 mt-1.5">Rp17.000</div>
                            </div>
                            <div class="bg-[#2d0707] p-3 rounded-xl border border-amber-500/30 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">🍜</span>
                                    <span class="text-[10px] font-bold text-amber-400 bg-black/40 px-1.5 py-0.5 rounded">Mie & Bakso</span>
                                </div>
                                <div class="text-xs font-bold text-white mt-1">Mie Ayam Bakso</div>
                                <div class="text-[10px] text-amber-200/60">Mie basah, ayam tumis, 2 bakso</div>
                                <div class="text-xs font-black text-amber-400 mt-1.5">Rp17.000</div>
                            </div>
                        </div>
                        <div class="bg-[#2d0707] p-3.5 rounded-xl border border-amber-500/30 text-xs">
                            <div class="flex justify-between font-black text-white pb-2 border-b border-amber-500/20">
                                <span>Total Pesanan (Soto Campur + Es Jeruk)</span>
                                <span class="text-amber-400">Rp27.000</span>
                            </div>
                            <div class="text-[10px] text-emerald-400 font-semibold mt-1.5 flex items-center gap-1.5">
                                <span>✓</span>
                                <span>BOM Resep terpotong otomatis: Daging ayam 80g, beras 150g, krupuk udang koya 15g</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: AI Executive Insight (5 Cols) -->
                    <div class="lg:col-span-5 bg-gradient-to-br from-[#380a0a] to-[#200404] text-white rounded-2xl p-4 flex flex-col justify-between border border-amber-500/30 shadow-lg">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-black uppercase tracking-wider text-amber-300">AI Resto Co-Pilot</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/40">Skor 92/100</span>
                            </div>
                            <h3 class="text-sm font-bold text-white leading-snug mb-2">
                                Rekomendasi Menu & Efisiensi Bahan SLL
                            </h3>
                            <p class="text-xs text-amber-100/80 leading-relaxed mb-3">
                                "Margin laba kotor hari ini stabil di 54.2%. Kombinasi menu <strong class="text-white">Soto Daging</strong> dan <strong class="text-white">Es Jeruk Segar</strong> sangat diminati. Pertahankan stok daging sapi segar untuk rush hour siang."
                            </p>
                        </div>
                        <div class="bg-black/30 rounded-xl p-2.5 border border-amber-500/20 text-[11px] flex items-center justify-between">
                            <span class="text-amber-200/80">Stok Kritis Koya & Sayap:</span>
                            <span class="font-bold text-emerald-300">Aman Terkendali</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid Section -->
        <section class="py-16 bg-[#160202] border-t border-b border-amber-500/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mb-3">
                        Dirancang Khusus untuk Operasional Kuliner Cepat
                    </h2>
                    <p class="text-sm text-amber-200/70">
                        Kecepatan kasir tanpa jeda, resep bahan baku presisi, dan konsultasi bisnis cerdas berbasis AI.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="bg-[#240606] rounded-3xl p-6 border border-amber-500/20 hover:border-amber-500/50 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xl mb-4 shadow-sm shadow-orange-500/30">
                            ⚡
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Kasir 0ms Latency (Offline-First)</h3>
                        <p class="text-xs text-amber-200/70 leading-relaxed">
                            Menggunakan Alpine.js dan IndexedDB (Dexie.js). Klik menu Soto Ayam, tambah ceker, dan hitung total seketika tanpa round-trip server. Tetap dapat transaksi saat internet mati.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-[#240606] rounded-3xl p-6 border border-amber-500/20 hover:border-amber-500/50 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xl mb-4 shadow-sm shadow-orange-500/30">
                            🍲
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Resep Bahan Baku (BOM) & HPP Riil</h3>
                        <p class="text-xs text-amber-200/70 leading-relaxed">
                            Satu porsi Soto Ayam terjual otomatis memotong 80g daging ayam, kuah kaldu, dan 15g krupuk koya. HPP dihitung berbasis Moving Average untuk memantau laba kotor riil.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-[#240606] rounded-3xl p-6 border border-amber-500/20 hover:border-amber-500/50 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xl mb-4 shadow-sm shadow-orange-500/30">
                            🤖
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">AI Resto Business Consultant</h3>
                        <p class="text-xs text-amber-200/70 leading-relaxed">
                            Agregasi metrik otomatis merangkum penjualan soto, mie ayam, & minuman ke AI LLM. Dapatkan evaluasi health score dan rekomendasi taktis harian untuk outlet Anda.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-[#240606] rounded-3xl p-6 border border-amber-500/20 hover:border-amber-500/50 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xl mb-4 shadow-sm shadow-orange-500/30">
                            🔒
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">4-Digit PIN Lock Kasir</h3>
                        <p class="text-xs text-amber-200/70 leading-relaxed">
                            Tablet di meja kasir dapat berganti shift staf dengan cepat cukup memasukkan 4-digit PIN tanpa perlu logout akun email. Laporan keuangan terlindungi aman.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-[#240606] rounded-3xl p-6 border border-amber-500/20 hover:border-amber-500/50 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xl mb-4 shadow-sm shadow-orange-500/30">
                            🖨️
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Printer Thermal & QRIS Dinamis</h3>
                        <p class="text-xs text-amber-200/70 leading-relaxed">
                            Cetak struk pesanan langsung ke printer thermal ESC/POS 58mm atau 80mm via Web Bluetooth API, serta terima pembayaran instan via QRIS Dinamis.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-[#240606] rounded-3xl p-6 border border-amber-500/20 hover:border-amber-500/50 hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xl mb-4 shadow-sm shadow-orange-500/30">
                            📊
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Snapshot Integritas Finansial</h3>
                        <p class="text-xs text-amber-200/70 leading-relaxed">
                            Setiap baris transaksi mengunci harga jual dan HPP modal pada saat transaksi terjadi. Laporan historis laba kotor selalu valid dan anti-manipulasi.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-[#120202] border-t border-amber-500/20 py-8 text-xs text-amber-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 text-amber-950 flex items-center justify-center font-black text-xs">SLL</span>
                <span class="font-bold text-white">SOTO LAMONGAN LESTARI</span>
                <span>— Pusat Kuliner Lamongan (Pesanan: +62 852-1325-4219)</span>
            </div>
            <div>
                <span>© {{ date('Y') }} SLL POS Pintar. Dibuat dengan arsitektur Laravel Monolith & TALL Stack.</span>
            </div>
        </div>
    </footer>

</body>
</html>
