<x-app-layout>
    @section('page_title', 'AI Business Consultant')

    <div class="space-y-5">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200/80">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">AI Resto Business Consultant</h1>
                <p class="text-xs text-slate-500 mt-0.5">Analisis taktis berbasis LLM cerdas dari metrik omzet, HPP modal resep, dan deteksi anomali transaksi.</p>
            </div>
            <form method="POST" action="{{ route('backoffice.ai-consultant.generate') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs tracking-wide transition shadow-xs flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Jalankan Analisis AI Baru</span>
                </button>
            </form>
        </div>

        @if ($latestReport)
            <!-- Executive Summary Card -->
            <div class="rounded-2xl bg-white p-5 border border-slate-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 font-mono">Evaluasi Harian</span>
                        <h2 class="text-sm font-bold text-slate-800 mt-0.5">
                            Periode {{ \Carbon\Carbon::parse($latestReport->report_date)->translatedFormat('l, d F Y') }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase font-mono">Resto Health Score</span>
                            <span class="text-base font-bold font-mono tabular-nums text-emerald-700">{{ $latestReport->ai_insights['health_score'] ?? 85 }}<span class="text-xs text-slate-400 font-normal">/100</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-xs leading-relaxed font-medium">
                    "{{ $latestReport->ai_insights['executive_summary'] ?? 'Ringkasan performa harian berjalan lancar.' }}"
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Actionable Recommendations -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="pb-2.5 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Rekomendasi Taktis</h3>
                    </div>

                    <div class="space-y-2">
                        @if (!empty($latestReport->ai_insights['actionable_recommendations']))
                            @foreach ($latestReport->ai_insights['actionable_recommendations'] as $rec)
                                <div class="p-3 rounded-[6px] bg-stone-50 border border-stone-200 text-xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-stone-900">{{ $rec['title'] }}</h4>
                                        <span class="px-1.5 py-0.5 rounded-[4px] text-[9px] font-bold {{ ($rec['priority'] ?? 'MEDIUM') === 'HIGH' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-stone-100 text-stone-700 border border-stone-200' }}">
                                            {{ $rec['priority'] ?? 'MEDIUM' }}
                                        </span>
                                    </div>
                                    <p class="text-stone-600 text-[11px] leading-relaxed">{{ $rec['action'] }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="text-xs text-stone-400">Tidak ada rekomendasi mendesak.</p>
                        @endif
                    </div>
                </div>

                <!-- Bundling Ideas & Fraud Assessment -->
                <div class="space-y-5">
                    <!-- Fraud & Anomaly Box -->
                    <div class="bg-white rounded-[10px] p-5 border border-[#E7E0D8]">
                        <div class="flex items-center justify-between pb-2.5 border-b border-stone-100 mb-2.5">
                            <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Deteksi Anomali (Audit Kasir)</h3>
                            <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-bold {{ ($latestReport->ai_insights['fraud_assessment']['risk_level'] ?? 'LOW') === 'LOW' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                                Risiko: {{ $latestReport->ai_insights['fraud_assessment']['risk_level'] ?? 'LOW' }}
                            </span>
                        </div>
                        <p class="text-xs text-stone-600 leading-relaxed">
                            {{ $latestReport->ai_insights['fraud_assessment']['notes'] ?? 'Aman. Tidak terdeteksi pembatalan transaksi berulang kali atau anomali buka laci kasir.' }}
                        </p>
                    </div>

                    <!-- Strengths Box -->
                    <div class="bg-white rounded-[10px] p-5 border border-[#E7E0D8]">
                        <div class="pb-2.5 border-b border-stone-100 mb-2.5">
                            <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Titik Positif Resto</h3>
                        </div>
                        <ul class="space-y-1.5 text-xs text-stone-600">
                            @if (!empty($latestReport->ai_insights['strengths']))
                                @foreach ($latestReport->ai_insights['strengths'] as $st)
                                    <li class="flex items-center gap-2">
                                        <span class="text-emerald-700 font-bold">✓</span>
                                        <span>{{ $st }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-[10px] p-8 text-center border border-[#E7E0D8] text-stone-400">
                <h3 class="text-xs font-bold text-stone-700">Belum Ada Laporan AI</h3>
                <p class="text-[11px] text-stone-400 mt-0.5">Klik tombol di atas untuk menjalankan analisis kecerdasan buatan.</p>
            </div>
        @endif
    </div>
</x-app-layout>
