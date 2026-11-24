<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLL - Soto Lamongan Lestari • Smart Cloud POS & AI Resto Consultant</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-[#F8FAFC] text-slate-800 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Top Navigation (Crisp Clean White) -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-stone-200/80 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 text-white flex items-center justify-center font-black text-2xl shadow-md shadow-orange-500/20 border border-amber-300/50 group-hover:scale-105 transition transform">
                    <span class="tracking-tighter drop-shadow-xs font-black">SLL</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-black text-base text-stone-900 tracking-tight">SOTO LAMONGAN LESTARI</span>
                        <span class="px-2 py-0.5 text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-300 rounded-full tracking-wider uppercase">Resto POS</span>
                    </div>
                    <p class="text-[11px] text-stone-500 font-medium">Pusat Kuliner Lamongan • Pesanan: +62 852-1325-4219</p>
                </div>
            </a>

            <!-- Nav Links & Actions -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Backoffice</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Layar Kasir (POS)</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-emerald-700 rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1">
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
            <div class="text-center max-w-3xl mx-auto relative z-10">
                <!-- Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100/70 border border-amber-300 text-amber-900 text-xs font-bold mb-6 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>SOTO LAMONGAN LESTARI • SMART CLOUD POS & AI RESTO</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-stone-900 tracking-tight leading-[1.15] mb-6">
                    Aplikasi Kasir <span class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 bg-clip-text text-transparent">0ms Latency</span> dengan Konsultan AI Resto
                </h1>

                <!-- Subheadline -->
                <p class="text-base sm:text-lg text-stone-600 mb-8 leading-relaxed">
                    Didesain khusus untuk operasional Soto Lamongan Lestari. Kasir tetap bertransaksi 100% cepat saat internet offline, stok resep kuah kaldu, daging, & koya terpotong otomatis, dan laporan laba kotor dihitung akurat.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 mb-10">
                    <a href="{{ route('pos.index') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm tracking-wide shadow-xs transition flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Buka Layar Kasir (0ms Offline-First)</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-800 font-semibold text-sm border border-slate-200 shadow-xs transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Dashboard Backoffice Resto</span>
                    </a>
                </div>

                <!-- 1-Click Demo Login Helper -->
                <div class="inline-flex flex-wrap items-center justify-center gap-2 sm:gap-3 px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-xs text-slate-600 shadow-xs">
                    <span class="font-bold text-slate-800">Akun Siap Pakai:</span>
                    <span>Admin: <code class="text-emerald-800 font-mono font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">admin@sotolamongan.com</code> (PIN 1234)</span>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <span>Kasir: <code class="text-emerald-800 font-mono font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">kasir@sotolamongan.com</code> (PIN 1234)</span>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <span>Pass: <code class="text-slate-800 font-mono font-bold">password</code></span>
                </div>
            </div>

            <!-- Interactive UI Preview Showcase Mockup (Clean White Card Surface) -->
            <div class="mt-14 max-w-5xl mx-auto rounded-3xl bg-white border border-stone-200 p-4 sm:p-6 shadow-[0_20px_50px_rgba(245,158,11,0.06)] relative z-10">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 text-xs font-bold text-stone-700">Soto Lamongan Lestari • Terminal Kasir & AI Resto Live Preview</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            🟢 Online & Auto-Sync Ready
                        </span>
                    </div>
                </div>

                <!-- Split Preview: Cashier POS + AI Consultant -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- Left: Cashier Preview (7 Cols) -->
                    <div class="lg:col-span-7 bg-[#fbf9f5] rounded-2xl p-4 border border-stone-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-extrabold text-stone-800 uppercase tracking-wider">Katalog Menu SLL (Alpine.js + Dexie)</span>
                            <span class="text-[11px] font-semibold text-amber-700">Cabang Utama • Order: 0852-1325-4219</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-white p-3 rounded-xl border border-stone-200 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-base">🍲</span>
                                    <span class="text-[10px] font-bold text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Soto</span>
                                </div>
                                <div class="text-xs font-black text-stone-900 mt-1">Soto Ayam Campur Nasi</div>
                                <div class="text-[10px] text-stone-500">Ayam suwir, tauge, soun, koya</div>
                                <div class="text-xs font-black text-amber-700 mt-1.5">Rp17.000</div>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-stone-200 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-base">🍜</span>
                                    <span class="text-[10px] font-bold text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Mie & Bakso</span>
                                </div>
                                <div class="text-xs font-black text-stone-900 mt-1">Mie Ayam Bakso</div>
                                <div class="text-[10px] text-stone-500">Mie basah, ayam tumis, 2 bakso</div>
                                <div class="text-xs font-black text-amber-700 mt-1.5">Rp17.000</div>
                            </div>
                        </div>
                        <div class="bg-white p-3.5 rounded-xl border border-stone-200 text-xs">
                            <div class="flex justify-between font-black text-stone-900 pb-2 border-b border-stone-100">
                                <span>Total Pesanan (Soto Campur + Es Jeruk)</span>
                                <span class="text-amber-700">Rp27.000</span>
                            </div>
                            <div class="text-[10px] text-emerald-700 font-semibold mt-1.5 flex items-center gap-1.5">
                                <span>✓</span>
                                <span>BOM Resep terpotong otomatis: Daging ayam 80g, beras 150g, krupuk udang koya 15g</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: AI Executive Insight (5 Cols) -->
                    <div class="lg:col-span-5 bg-gradient-to-br from-stone-900 via-stone-800 to-stone-900 text-white rounded-2xl p-4 flex flex-col justify-between shadow-sm">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-black uppercase tracking-wider text-amber-300">AI Resto Co-Pilot</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/40">Skor 92/100</span>
                            </div>
                            <h3 class="text-sm font-bold text-white leading-snug mb-2">
                                Rekomendasi Menu & Efisiensi Bahan SLL
                            </h3>
                            <p class="text-xs text-stone-300 leading-relaxed mb-3">
                                "Margin laba kotor hari ini stabil di 54.2%. Kombinasi menu <strong class="text-white">Soto Daging</strong> dan <strong class="text-white">Es Jeruk Segar</strong> sangat diminati. Pertahankan stok daging sapi segar untuk rush hour siang."
                            </p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-2.5 text-[11px] flex items-center justify-between">
                            <span class="text-stone-300">Stok Kritis Koya & Sayap:</span>
                            <span class="font-bold text-emerald-300">Aman Terkendali</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid Section (Clean White Background) -->
        <section class="py-16 bg-white border-t border-b border-stone-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight mb-3">
                        Dirancang Khusus untuk Operasional Kuliner Cepat
                    </h2>
                    <p class="text-sm text-stone-600">
                        Kecepatan kasir tanpa jeda, resep bahan baku presisi, dan konsultasi bisnis cerdas berbasis AI.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="bg-[#fdfbf7] rounded-3xl p-6 border border-amber-200/80 hover:border-amber-400 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4 shadow-2xs border border-amber-200">
                            ⚡
                        </div>
                        <h3 class="text-base font-bold text-stone-900 mb-2">Kasir 0ms Latency (Offline-First)</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Menggunakan Alpine.js dan IndexedDB (Dexie.js). Klik menu Soto Ayam, tambah ceker, dan hitung total seketika tanpa round-trip server. Tetap dapat transaksi saat internet mati.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-[#fdfbf7] rounded-3xl p-6 border border-amber-200/80 hover:border-amber-400 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4 shadow-2xs border border-amber-200">
                            🍲
                        </div>
                        <h3 class="text-base font-bold text-stone-900 mb-2">Resep Bahan Baku (BOM) & HPP Riil</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Satu porsi Soto Ayam terjual otomatis memotong 80g daging ayam, kuah kaldu, dan 15g krupuk koya. HPP dihitung berbasis Moving Average untuk memantau laba kotor riil.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-[#fdfbf7] rounded-3xl p-6 border border-amber-200/80 hover:border-amber-400 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4 shadow-2xs border border-amber-200">
                            🤖
                        </div>
                        <h3 class="text-base font-bold text-stone-900 mb-2">AI Resto Business Consultant</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Agregasi metrik otomatis merangkum penjualan soto, mie ayam, & minuman ke AI LLM. Dapatkan evaluasi health score dan rekomendasi taktis harian untuk outlet Anda.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-[#fdfbf7] rounded-3xl p-6 border border-amber-200/80 hover:border-amber-400 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4 shadow-2xs border border-amber-200">
                            🔒
                        </div>
                        <h3 class="text-base font-bold text-stone-900 mb-2">4-Digit PIN Lock Kasir</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Tablet di meja kasir dapat berganti shift staf dengan cepat cukup memasukkan 4-digit PIN tanpa perlu logout akun email. Laporan keuangan terlindungi aman.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-[#fdfbf7] rounded-3xl p-6 border border-amber-200/80 hover:border-amber-400 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4 shadow-2xs border border-amber-200">
                            🖨️
                        </div>
                        <h3 class="text-base font-bold text-stone-900 mb-2">Printer Thermal & QRIS Dinamis</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Cetak struk pesanan langsung ke printer thermal ESC/POS 58mm atau 80mm via Web Bluetooth API, serta terima pembayaran instan via QRIS Dinamis.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-[#fdfbf7] rounded-3xl p-6 border border-amber-200/80 hover:border-amber-400 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4 shadow-2xs border border-amber-200">
                            📊
                        </div>
                        <h3 class="text-base font-bold text-stone-900 mb-2">Snapshot Integritas Finansial</h3>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            Setiap baris transaksi mengunci harga jual dan HPP modal pada saat transaksi terjadi. Laporan historis laba kotor selalu valid dan anti-manipulasi.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-stone-200 py-8 text-xs text-stone-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black text-xs">SLL</span>
                <span class="font-bold text-stone-800">SOTO LAMONGAN LESTARI</span>
                <span>— Pusat Kuliner Lamongan (Pesanan: +62 852-1325-4219)</span>
            </div>
            <div>
                <span>© {{ date('Y') }} SLL POS Pintar. Dibuat dengan arsitektur Laravel Monolith & TALL Stack.</span>
            </div>
        </div>
    </footer>

</body>
</html>
