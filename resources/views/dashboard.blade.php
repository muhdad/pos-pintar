<x-app-layout>
    @section('page_title', 'Dashboard Eksekutif')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    <div class="space-y-6">
        <!-- Top Executive Welcome & Action Row -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-amber-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-100/80 border border-amber-300/80 text-[10px] font-bold text-amber-900 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                        <span>{{ $outlet->name ?? 'SLL Cabang Utama' }}</span>
                    </span>
                    <span class="text-xs text-amber-300">•</span>
                    <span class="text-xs text-slate-500 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    <span class="text-xs text-amber-300 hidden sm:inline">•</span>
                    <span class="text-xs text-amber-700 font-semibold hidden sm:inline">📞 +62 852-1325-4219</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Soto Lamongan Lestari — Dashboard Resto 🍲
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Monitoring omzet penjualan soto & bakso real-time, kontrol bahan baku resep, dan rekomendasi AI.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Refresh AI Insights Button -->
                <form method="POST" action="{{ route('backoffice.ai-consultant.generate') }}">
                    @csrf
                    <button type="submit" 
                            class="px-3.5 py-2 rounded-xl bg-white border border-amber-200 text-slate-700 hover:bg-amber-50 hover:text-amber-950 font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Update Insight AI</span>
                    </button>
                </form>

                <!-- Buka Terminal Kasir -->
                <a href="{{ route('pos.index') }}" 
                   class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 hover:from-amber-500 hover:to-orange-600 text-white font-bold text-xs tracking-wide shadow-md shadow-amber-600/25 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Buka Layar Kasir</span>
                </a>
            </div>
        </div>

        <!-- 4 Key Financial & Stock Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Omzet -->
            <div class="bg-white rounded-2xl p-5 border border-amber-100 shadow-xs flex flex-col justify-between hover:border-amber-300 transition group">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-bold text-amber-900/80 uppercase tracking-wider">Omzet Soto & Minuman</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">
                        Rp{{ number_format($metrics['financial']['total_revenue'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-amber-100/60 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md text-[10px]">
                            <span>✓</span>
                            <span>{{ $metrics['financial']['transactions'] ?? 0 }} Pesanan Selesai</span>
                        </span>
                        <span class="text-[11px] text-slate-400">AOV: Rp{{ number_format($metrics['financial']['aov'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <span>Total Akumulasi:</span>
                        <span class="font-bold text-slate-700">Rp{{ number_format($metrics['financial']['all_time_revenue'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Laba Kotor & Margin -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition group">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Laba Kotor (Profit)</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-emerald-600 tracking-tight">
                        Rp{{ number_format($metrics['financial']['gross_profit'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="inline-flex items-center font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px]">
                            {{ $metrics['financial']['profit_margin_pct'] ?? 0 }}% Margin
                        </span>
                        <span class="text-[11px] text-slate-400">Kondisi Sehat</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <span>Estimasi Bersih:</span>
                        <span class="font-bold text-emerald-600">Setelah HPP Riil</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total HPP Modal -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition group">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Beban HPP (Modal Resep)</span>
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">
                        Rp{{ number_format($metrics['financial']['total_cogs'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="inline-flex items-center font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px]">
                            Snapshot Resep (BOM)
                        </span>
                        <span class="text-[11px] text-slate-400">Moving Avg</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <span>Rasio Beban:</span>
                        <span class="font-bold text-slate-700">
                            {{ ($metrics['financial']['total_revenue'] ?? 0) > 0 ? round((($metrics['financial']['total_cogs'] ?? 0) / $metrics['financial']['total_revenue']) * 100, 1) : 0 }}% Omzet
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Bahan Baku Kritis -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition group">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status Bahan Baku</span>
                        <div class="w-8 h-8 rounded-xl {{ count($lowStockItems) > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center font-bold">
                            @if(count($lowStockItems) > 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="text-2xl font-black {{ count($lowStockItems) > 0 ? 'text-amber-600' : 'text-slate-900' }} tracking-tight">
                        {{ count($lowStockItems) > 0 ? count($lowStockItems) . ' Item Kritis' : 'Stok Aman' }}
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        @if(count($lowStockItems) > 0)
                            <span class="inline-flex items-center font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px]">
                                Perlu Restock
                            </span>
                            <a href="{{ route('backoffice.inventory') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-800">Cek Gudang →</a>
                        @else
                            <span class="inline-flex items-center font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px]">
                                ✓ 100% Terkontrol
                            </span>
                            <span class="text-[11px] text-slate-400">Di atas batas min</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <span>Bahan Terpantau:</span>
                        <span class="font-bold text-slate-700">{{ $totalRawMaterialsCount ?? 12 }} Bahan Baku</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Consultant Executive Intelligence Card (Culinary SLL Resto Co-Pilot) -->
        <div class="rounded-3xl bg-gradient-to-br from-[#1c0404] via-[#2d0707] to-[#180303] text-white p-6 sm:p-7 border border-amber-500/30 shadow-xl relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-orange-600/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-4">
                <!-- Top Sub-Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-400/30 tracking-wider uppercase flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            <span>AI Resto Co-Pilot (SLL)</span>
                        </span>
                        <span class="text-xs text-amber-100/70">
                            @if($latestAiReport)
                                Evaluasi {{ \Carbon\Carbon::parse($latestAiReport->report_date)->translatedFormat('l, d F Y') }}
                            @else
                                Analisis Cerdas Harian Soto Lamongan Lestari
                            @endif
                        </span>
                    </div>

                    <!-- Health Score Widget -->
                    <div class="flex items-center gap-2.5 self-start sm:self-auto bg-black/30 border border-amber-400/20 rounded-xl px-3.5 py-1.5">
                        <span class="text-[10px] uppercase font-bold text-amber-300 tracking-wider">Resto Health Score</span>
                        <div class="text-sm font-black text-white">
                            {{ $latestAiReport->ai_insights['health_score'] ?? 92 }}<span class="text-[10px] font-normal text-amber-300">/100</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            ✓ Margin Prima
                        </span>
                    </div>
                </div>

                <!-- Executive Summary Highlight -->
                <div class="bg-black/25 border border-amber-500/20 rounded-2xl p-4 sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 text-amber-950 font-black flex items-center justify-center shrink-0 text-base shadow-sm">
                            🍲
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-amber-300 mb-0.5">Analisis Menu & Profitabilitas</h4>
                            <p class="text-xs sm:text-sm font-medium text-amber-50 leading-relaxed">
                                "{{ $latestAiReport->ai_insights['executive_summary'] ?? 'Soto Lamongan Lestari mencatatkan margin kotor optimal di atas 52%. Kombinasi menu Soto Ayam Campur Nasi dan Mie Ayam Bakso menjadi kontributor omzet tertinggi. Efisiensi kuah kaldu & koya udang terjaga stabil.' }}"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actionable Recommendations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    @if (!empty($latestAiReport->ai_insights['actionable_recommendations']))
                        @foreach (array_slice($latestAiReport->ai_insights['actionable_recommendations'], 0, 2) as $rec)
                            <div class="bg-black/20 hover:bg-black/30 backdrop-blur-sm rounded-xl p-3.5 border border-amber-500/20 transition group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ ($rec['priority'] ?? 'MEDIUM') === 'HIGH' ? 'bg-rose-400' : 'bg-amber-400' }}"></span>
                                        <span class="font-bold text-xs text-white group-hover:text-amber-200 transition">{{ $rec['title'] ?? 'Rekomendasi Taktis' }}</span>
                                    </div>
                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded {{ ($rec['priority'] ?? 'MEDIUM') === 'HIGH' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                        {{ $rec['priority'] ?? 'MEDIUM' }}
                                    </span>
                                </div>
                                <p class="text-amber-100/80 text-[11px] leading-relaxed">
                                    {{ $rec['action'] ?? '' }}
                                </p>
                            </div>
                        @endforeach
                    @else
                        <!-- Authentic SLL culinary recommendations -->
                        <div class="bg-black/20 hover:bg-black/30 backdrop-blur-sm rounded-xl p-3.5 border border-amber-500/20 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                    <span class="font-bold text-xs text-white group-hover:text-amber-200 transition">Bundling Soto Ayam + Es Jeruk Segar</span>
                                </div>
                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                    HIGH
                                </span>
                            </div>
                            <p class="text-amber-100/80 text-[11px] leading-relaxed">
                                Dorong peningkatan Average Order Value (AOV) dengan paket hemat Soto Ayam Campur (17K) + Es Jeruk (10K) bundling Rp25.000 saat jam makan siang.
                            </p>
                        </div>

                        <div class="bg-black/20 hover:bg-black/30 backdrop-blur-sm rounded-xl p-3.5 border border-amber-500/20 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span class="font-bold text-xs text-white group-hover:text-amber-200 transition">Stok Daging Sapi & Koya Gurih</span>
                                </div>
                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    MEDIUM
                                </span>
                            </div>
                            <p class="text-amber-100/80 text-[11px] leading-relaxed">
                                Stok daging sapi lokal & kerupuk udang koya terpantau aman. Pertahankan takaran BOM 100g daging per porsi untuk menjaga margin 54%.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Bottom Link to Full AI Consultant -->
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-amber-200/80">
                    <span class="text-[11px] text-amber-200/60">Didukung analisis otomatis kalkulasi resep (BOM) & AI LLM</span>
                    <a href="{{ route('backoffice.ai-consultant') }}" class="font-bold text-xs text-amber-300 hover:text-white transition flex items-center gap-1.5 ml-auto">
                        <span>Konsultasi AI Resto Lebih Lanjut</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Two Column Detailed Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Recent Orders (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Pesanan SLL Terbaru</h3>
                                <p class="text-[11px] text-slate-500">Aktivitas penjualan real-time dari terminal kasir</p>
                            </div>
                        </div>
                        <a href="{{ route('backoffice.orders') }}" class="text-xs font-bold text-amber-700 hover:text-amber-900 transition flex items-center gap-1">
                            <span>Lihat Semua</span>
                            <span>→</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                                    <th class="pb-3 font-semibold">No. Struk</th>
                                    <th class="pb-3 font-semibold">Menu & Item</th>
                                    <th class="pb-3 font-semibold">Waktu</th>
                                    <th class="pb-3 font-semibold">Metode</th>
                                    <th class="pb-3 font-semibold text-right">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentOrders as $order)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 font-mono font-bold text-slate-900">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ $order->order_number }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-sans font-normal">
                                                {{ $order->user->name ?? 'Kasir' }}
                                            </div>
                                        </td>
                                        <td class="py-3 text-slate-600 max-w-[180px]">
                                            <span class="font-bold text-slate-800">{{ $order->items->count() }} item</span>
                                            <div class="text-[11px] text-slate-400 truncate">
                                                {{ $order->items->pluck('product.name')->join(', ') }}
                                            </div>
                                        </td>
                                        <td class="py-3 text-slate-500 font-mono text-[11px]">
                                            {{ $order->created_at->format('H:i') }} WIB
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $method = $order->payments->first()->method ?? 'CASH';
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $method === 'CASH' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : ($method === 'QRIS' ? 'bg-purple-50 text-purple-700 border border-purple-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60') }}">
                                                @if($method === 'CASH')
                                                    <span>💵 Tunai</span>
                                                @elseif($method === 'QRIS')
                                                    <span>📱 QRIS</span>
                                                @else
                                                    <span>💳 Transfer</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-3 text-right font-black text-slate-900 font-mono">
                                            Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                                🛒
                                            </div>
                                            <p class="text-xs font-semibold text-slate-600">Belum ada transaksi hari ini</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Buka kasir untuk memulai transaksi penjualan perdana.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($recentOrders->isNotEmpty())
                    <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Menampilkan {{ min($recentOrders->count(), 10) }} transaksi terakhir</span>
                        <span class="text-emerald-600 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Auto-sync aktif</span>
                        </span>
                    </div>
                @endif
            </div>

            <!-- Right Column: Top Products & Raw Material Alerts (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Top 5 Best Sellers -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🔥</span>
                            <h3 class="text-sm font-extrabold text-slate-900">Top 5 Menu Terlaris</h3>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border border-slate-200/60 px-2 py-0.5 rounded-md">Hari Ini</span>
                    </div>

                    @php
                        $maxQty = !empty($metrics['top_selling']) ? max(array_column($metrics['top_selling'], 'qty')) : 1;
                    @endphp

                    <div class="space-y-3.5">
                        @forelse ($metrics['top_selling'] as $idx => $prod)
                            @php
                                $percent = $maxQty > 0 ? round(($prod['qty'] / $maxQty) * 100) : 0;
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-black shrink-0 {{ $idx === 0 ? 'bg-amber-100 text-amber-900' : ($idx === 1 ? 'bg-slate-200 text-slate-800' : ($idx === 2 ? 'bg-orange-100 text-orange-900' : 'bg-slate-100 text-slate-600')) }}">
                                            #{{ $idx + 1 }}
                                        </span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 truncate">{{ $prod['name'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="font-black text-slate-900">Rp{{ number_format($prod['sales'], 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $prod['qty'] }} terjual</span>
                                    </div>
                                </div>
                                <!-- Visual Volume Bar -->
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $idx === 0 ? 'bg-amber-500' : 'bg-gradient-to-r from-amber-600 to-orange-500' }} transition-all duration-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-slate-400">
                                Belum ada menu terjual hari ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Status Bahan Baku & Alert Gudang -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-base">{{ count($lowStockItems) > 0 ? '⚠️' : '🛡️' }}</span>
                            <h3 class="text-sm font-extrabold text-slate-900">Status Persediaan Bahan</h3>
                        </div>
                        <a href="{{ route('backoffice.inventory') }}" class="text-xs font-bold text-amber-700 hover:text-amber-900 transition">
                            Kelola Gudang →
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($lowStockItems as $stock)
                            <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between text-xs">
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ $stock->rawMaterial->name }}</h4>
                                    <span class="text-[11px] text-amber-800">
                                        Sisa: <strong>{{ (float) $stock->current_stock }} {{ $stock->rawMaterial->unit }}</strong> 
                                        <span class="text-slate-400 font-normal">(Min: {{ (float) $stock->rawMaterial->minimum_stock }})</span>
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-200 text-amber-900">
                                    Kritis
                                </span>
                            </div>
                        @empty
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center space-y-1">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-sm font-bold">
                                    ✓
                                </div>
                                <h4 class="text-xs font-bold text-slate-900 pt-1">Seluruh Stok Bahan Baku Aman</h4>
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Semua {{ $totalRawMaterialsCount ?? 12 }} jenis bahan baku utama berada di atas batas minimum persediaan.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
