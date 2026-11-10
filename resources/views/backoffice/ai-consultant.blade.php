<x-app-layout>
    @section('page_title', 'AI Business Consultant')

    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">AI Business Consultant</h1>
                <p class="text-xs text-slate-500 mt-0.5">Analisis taktis berbasis LLM cerdas dari metrik omzet, HPP, resep, dan deteksi anomali fraud.</p>
            </div>
            <form method="POST" action="{{ route('backoffice.ai-consultant.generate') }}">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs tracking-wide shadow-md shadow-blue-500/25 transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Jalankan Analisis AI Baru</span>
                </button>
            </form>
        </div>

        @if ($latestReport)
            <!-- Executive Summary Card -->
            <div class="rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xl font-bold shadow-sm shadow-blue-500/20">
                            🤖
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Laporan Konsultan AI</span>
                            <h2 class="text-base font-extrabold text-slate-900">
                                Evaluasi {{ \Carbon\Carbon::parse($latestReport->report_date)->translatedFormat('l, d F Y') }}
                            </h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-bold text-slate-500 block uppercase">Health Score</span>
                            <span class="text-lg font-black text-blue-600">{{ $latestReport->ai_insights['health_score'] ?? 85 }}/100</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/70 text-slate-800 text-xs leading-relaxed font-medium">
                    "{{ $latestReport->ai_insights['executive_summary'] ?? 'Ringkasan performa harian berjalan lancar.' }}"
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Actionable Recommendations -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="text-base">🎯</span>
                        <h3 class="text-sm font-extrabold text-slate-900">Rekomendasi Tindakan Taktis</h3>
                    </div>

                    <div class="space-y-3">
                        @if (!empty($latestReport->ai_insights['actionable_recommendations']))
                            @foreach ($latestReport->ai_insights['actionable_recommendations'] as $rec)
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-slate-900">{{ $rec['title'] }}</h4>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold {{ ($rec['priority'] ?? 'MEDIUM') === 'HIGH' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $rec['priority'] ?? 'MEDIUM' }}
                                        </span>
                                    </div>
                                    <p class="text-slate-600 text-[11px] leading-relaxed">{{ $rec['action'] }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400">Tidak ada rekomendasi mendesak.</p>
                        @endif
                    </div>
                </div>

                <!-- Bundling Ideas & Fraud Assessment -->
                <div class="space-y-6">
                    <!-- Fraud & Anomaly Box -->
                    <div class="bg-white/90 backdrop-blur-md rounded-3xl p-6 border border-blue-100/80 shadow-[0_4px_25px_rgb(0,0,0,0.03)]">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🛡️</span>
                                <h3 class="text-sm font-extrabold text-slate-900">Deteksi Kecurangan (Fraud Alert)</h3>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ ($latestReport->ai_insights['fraud_assessment']['risk_level'] ?? 'LOW') === 'LOW' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                Risiko: {{ $latestReport->ai_insights['fraud_assessment']['risk_level'] ?? 'LOW' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $latestReport->ai_insights['fraud_assessment']['notes'] ?? 'Aman. Tidak terdeteksi pembatalan transaksi berulang kali atau anomali buka laci uang.' }}
                        </p>
                    </div>

                    <!-- Strengths Box -->
                    <div class="bg-white/90 backdrop-blur-md rounded-3xl p-6 border border-blue-100/80 shadow-[0_4px_25px_rgb(0,0,0,0.03)]">
                        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-3">
                            <span class="text-base">✨</span>
                            <h3 class="text-sm font-extrabold text-slate-900">Kekuatan & Titik Positif Bisnis</h3>
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-600">
                            @if (!empty($latestReport->ai_insights['strengths']))
                                @foreach ($latestReport->ai_insights['strengths'] as $st)
                                    <li class="flex items-center gap-2">
                                        <span class="text-emerald-500 font-bold">✓</span>
                                        <span>{{ $st }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white/90 rounded-3xl p-12 text-center border border-blue-100 text-slate-400">
                <div class="text-4xl mb-3">🤖</div>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Laporan AI</h3>
                <p class="text-xs text-slate-400 mt-1">Klik tombol di atas untuk menjalankan analisa kecerdasan buatan.</p>
            </div>
        @endif
    </div>
</x-app-layout>

