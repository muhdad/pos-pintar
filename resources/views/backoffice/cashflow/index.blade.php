<x-app-layout>
    @section('page_title', 'Arus Kas & Buku Keuangan')

    <div x-data="{
        expenseModalOpen: false,
        incomeModalOpen: false,
        receiptPreviewModal: false,
        previewImageUrl: '',
        openReceiptPreview(url) {
            this.previewImageUrl = url;
            this.receiptPreviewModal = true;
        }
    }" class="space-y-6">

        <!-- Top Header & Date Selector Strip (Pastel Theme) -->
        <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 font-mono">Manajemen Kas & Likuiditas Toko</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-display font-bold text-[#5B0A14] tracking-tight">
                    Arus Kas & Pembukuan Keuangan
                </h1>
                <p class="text-xs text-stone-500 mt-1">
                    Pencatatan real-time uang masuk kasir, belanja bahan baku dapur, dan kontrol sisa saldo operasional.
                </p>
            </div>

            <!-- Controls: Date Picker & Quick Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('backoffice.cashflow.index') }}" class="flex items-center gap-1.5 bg-stone-50 p-1 rounded-xl border border-stone-200">
                    <label class="text-[11px] font-bold text-stone-600 pl-2">Tanggal:</label>
                    <input type="date" 
                           name="date" 
                           value="{{ request('date', $selectedDate) }}" 
                           onchange="this.form.submit()" 
                           class="bg-white border-0 rounded-lg text-xs font-mono py-1 px-2.5 text-stone-800 focus:ring-1 focus:ring-emerald-500 font-semibold">
                </form>

                <button type="button" 
                        @click="incomeModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow transition flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Pemasukan</span>
                </button>

                <button type="button" 
                        @click="expenseModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm hover:shadow transition flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                    </svg>
                    <span>+ Pengeluaran</span>
                </button>

                <a href="{{ route('backoffice.inventory') }}" 
                   class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm hover:shadow transition flex items-center gap-1.5 active:translate-y-px"
                   title="Pindai nota pasar otomatis dengan OCR AI">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                    <span>Foto Struk AI</span>
                </a>

                <a href="{{ route('backoffice.cashflow.export-excel', request()->query()) }}" 
                   class="px-3.5 py-2 rounded-xl bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs border border-stone-200 shadow-sm transition flex items-center gap-1.5 active:translate-y-px"
                   title="Export Seluruh Laporan Arus Kas ke File Excel">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>

        <!-- 3 CORE HERO METRICS: POINT 1 (IN) - POINT 2 (OUT) - POINT 3 (NET SISA KEUANGAN) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- 1. UANG MASUK TERCATAT (INFLOW) -->
            <div class="bg-gradient-to-br from-emerald-50/90 via-teal-50/50 to-white rounded-2xl border border-emerald-200/80 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800/80 font-mono block">1. UANG MASUK (IN)</span>
                            <span class="text-xs font-semibold text-stone-600">{{ $metrics['today_date'] }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        TOTAL MASUK
                    </span>
                </div>

                <div class="text-2xl sm:text-3xl font-mono font-bold text-emerald-700 tracking-tight">
                    Rp {{ number_format($metrics['total_today_inflow'], 0, ',', '.') }}
                </div>

                <div class="mt-4 pt-3 border-t border-emerald-200/60 space-y-1.5 text-xs text-stone-600">
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Omzet Penjualan POS Kasir:</span>
                        </span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($metrics['omzet_hari_ini'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                            <span>Pemasukan Manual / Modal:</span>
                        </span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($metrics['today_manual_income'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-stone-500 pt-1 border-t border-emerald-100">
                        <span>Pembanding Kemarin (H-1):</span>
                        <span class="font-mono font-semibold">Rp {{ number_format($metrics['omzet_h1'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. PENGELUARAN TERCATAT (OUTFLOW) -->
            <div class="bg-gradient-to-br from-rose-50/90 via-orange-50/40 to-white rounded-2xl border border-rose-200/80 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-rose-800/80 font-mono block">2. PENGELUARAN (OUT)</span>
                            <span class="text-xs font-semibold text-stone-600">{{ $metrics['today_date'] }}</span>
                        </div>
                    </div>
                    @php
                        $rasio = $metrics['rasio_belanja_h1'];
                        $rasioClass = $rasio > 75 ? 'bg-red-100 text-red-800 border-red-200' : ($rasio > 45 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200');
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $rasioClass }}">
                        {{ $rasio }}% DARI H-1
                    </span>
                </div>

                <div class="text-2xl sm:text-3xl font-mono font-bold text-rose-700 tracking-tight">
                    Rp {{ number_format($metrics['total_today_outflow'], 0, ',', '.') }}
                </div>

                <div class="mt-4 pt-3 border-t border-rose-200/60 space-y-1.5 text-xs text-stone-600">
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>Belanja Bahan Baku (Restock):</span>
                        </span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($metrics['today_restock'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            <span>Biaya Operasional & Kas Kecil:</span>
                        </span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($metrics['today_operational'] + $metrics['today_other_expense'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-stone-500 pt-1 border-t border-rose-100">
                        <span>Porsi Belanja Terhadap Omzet:</span>
                        <span class="font-mono font-semibold">{{ $rasio }}% (Batas Aman &le; 45%)</span>
                    </div>
                </div>
            </div>

            <!-- 3. IN - OUT SISA KEUANGAN AKHIR (NET BALANCE) -->
            <div class="bg-gradient-to-br from-indigo-50/90 via-sky-50/50 to-white rounded-2xl border border-indigo-200/80 p-5 shadow-sm hover:shadow-md transition relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-800/80 font-mono block">3. IN - OUT SISA KEUANGAN</span>
                            <span class="text-xs font-semibold text-stone-600">Saldo Kas Bersih</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $metrics['net_balance_today'] >= 0 ? 'bg-indigo-100 text-indigo-800 border-indigo-200' : 'bg-red-100 text-red-800 border-red-200' }} border">
                        {{ $metrics['net_balance_today'] >= 0 ? 'SURPLUS BERSIH' : 'DEFISIT KAS' }}
                    </span>
                </div>

                <div class="text-2xl sm:text-3xl font-mono font-bold {{ $metrics['net_balance_today'] >= 0 ? 'text-indigo-900' : 'text-red-600' }} tracking-tight">
                    {{ $metrics['net_balance_today'] < 0 ? '-' : '' }}Rp {{ number_format(abs($metrics['net_balance_today']), 0, ',', '.') }}
                </div>

                <div class="mt-4 pt-3 border-t border-indigo-200/60 space-y-1.5 text-xs text-stone-600">
                    <div class="flex justify-between items-center">
                        <span>Formula:</span>
                        <span class="font-mono text-[11px] text-stone-500">Uang Masuk - Uang Keluar</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Sisa Kas Omzet H-1:</span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($metrics['sisa_kas_h1'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] text-stone-500 pt-1 border-t border-indigo-100">
                        <span>Akumulasi Bersih Bulan Ini:</span>
                        <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($metrics['retained_monthly_fund'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4-Col Quick Information Badges (Soft Pastel) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card A: Omzet H-1 Tunai vs Non-Tunai -->
            <div class="bg-white rounded-2xl border border-stone-200/70 p-4 shadow-sm hover:border-stone-300 transition">
                <div class="text-[10px] font-bold uppercase tracking-wider text-stone-400 font-mono mb-1">METODE PEMBAYARAN H-1</div>
                <div class="text-lg font-mono font-bold text-stone-800">Rp {{ number_format($metrics['omzet_h1'], 0, ',', '.') }}</div>
                <div class="mt-2.5 pt-2 border-t border-stone-100 text-[11px] text-stone-500 space-y-1">
                    <div class="flex justify-between">
                        <span>Tunai (Cash):</span>
                        <span class="font-bold text-stone-800 font-mono">Rp {{ number_format($metrics['omzet_h1_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>QRIS / Non-Tunai:</span>
                        <span class="font-bold text-stone-800 font-mono">Rp {{ number_format($metrics['omzet_h1_non_cash'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card B: Belanja Hari Ini Dipotong dari H-1 -->
            <div class="bg-white rounded-2xl border border-stone-200/70 p-4 shadow-sm hover:border-stone-300 transition">
                <div class="text-[10px] font-bold uppercase tracking-wider text-stone-400 font-mono mb-1">PERPUTARAN OMZET H-1</div>
                @if($metrics['defisit_h1'] > 0)
                    <div class="text-lg font-mono font-bold text-rose-600">Defisit Rp {{ number_format($metrics['defisit_h1'], 0, ',', '.') }}</div>
                    <p class="text-[10px] text-rose-500 font-medium mt-1">Belanja bahan melebihi omzet H-1.</p>
                @else
                    <div class="text-lg font-mono font-bold text-emerald-700">Sisa Rp {{ number_format($metrics['sisa_kas_h1'], 0, ',', '.') }}</div>
                    <p class="text-[10px] text-emerald-600 font-medium mt-1">Kas operasional H-1 masih surplus.</p>
                @endif
                <div class="mt-2.5 pt-2 border-t border-stone-100 text-[11px] text-stone-500 flex justify-between">
                    <span>Total Belanja:</span>
                    <span class="font-bold text-rose-600 font-mono">Rp {{ number_format($metrics['total_today_expense'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Card C: Total Omzet Bulan Ini -->
            <div class="bg-white rounded-2xl border border-stone-200/70 p-4 shadow-sm hover:border-stone-300 transition">
                <div class="text-[10px] font-bold uppercase tracking-wider text-stone-400 font-mono mb-1">TOTAL MASUK BULAN INI</div>
                <div class="text-lg font-mono font-bold text-stone-800">Rp {{ number_format($metrics['total_month_inflow'], 0, ',', '.') }}</div>
                <div class="mt-2.5 pt-2 border-t border-stone-100 text-[11px] text-stone-500 space-y-1">
                    <div class="flex justify-between">
                        <span>Penjualan POS:</span>
                        <span class="font-bold text-stone-800 font-mono">Rp {{ number_format($metrics['month_revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pemasukan Manual:</span>
                        <span class="font-bold text-stone-800 font-mono">Rp {{ number_format($metrics['month_manual_income'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card D: Saldo Akumulasi Penarikan Bulanan -->
            <div class="bg-gradient-to-br from-amber-50/80 via-yellow-50/40 to-white rounded-2xl border border-amber-200/80 p-4 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 font-mono">PENARIKAN 1 BULAN</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">BULANAN</span>
                </div>
                <div class="text-lg font-mono font-bold text-amber-900">Rp {{ number_format($metrics['retained_monthly_fund'], 0, ',', '.') }}</div>
                <div class="mt-2.5 pt-2 border-t border-amber-200/60 text-[11px] text-stone-600 flex justify-between">
                    <span>Pengeluaran Bulan Ini:</span>
                    <span class="font-bold text-rose-600 font-mono">-Rp {{ number_format($metrics['month_expense'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visual Pastel Pie Chart & Flow Explanation -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: SVG Pie Chart with Eye-Pleasing Pastel Tones -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                            <h2 class="text-sm font-display font-bold text-stone-900 tracking-tight">
                                Diagram Perputaran Kas
                            </h2>
                        </div>
                        <span class="text-[11px] text-stone-500">Distribusi Alokasi Belanja & Sisa Kas H-1</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-700">100% Basis</span>
                </div>

                <div class="flex flex-col items-center justify-center py-2">
                    <!-- SVG Donut / Pie Graphic in Pastel Tones -->
                    <div class="relative w-48 h-48 sm:w-52 sm:h-52">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                            <!-- Background Track -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="#F1F5F9" 
                                    stroke-width="18" />

                            <!-- Slice 1: Belanja Bahan Baku (Pastel Sky Blue #38BDF8) -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="{{ $metrics['pie_chart']['restock']['color'] }}" 
                                    stroke-width="18"
                                    stroke-dasharray="{{ $metrics['pie_chart']['restock']['stroke_length'] }} {{ $metrics['pie_chart']['circumference'] }}"
                                    stroke-dashoffset="{{ $metrics['pie_chart']['restock']['stroke_offset'] }}" 
                                    class="transition-all duration-500" />

                            <!-- Slice 2: Biaya Operasional (Pastel Coral/Rose #FB7185) -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="{{ $metrics['pie_chart']['operational']['color'] }}" 
                                    stroke-width="18"
                                    stroke-dasharray="{{ $metrics['pie_chart']['operational']['stroke_length'] }} {{ $metrics['pie_chart']['circumference'] }}"
                                    stroke-dashoffset="{{ $metrics['pie_chart']['operational']['stroke_offset'] }}" 
                                    class="transition-all duration-500" />

                            <!-- Slice 3: Sisa Kas H-1 (Pastel Emerald Mint #34D399) -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="{{ $metrics['pie_chart']['retained']['color'] }}" 
                                    stroke-width="18"
                                    stroke-dasharray="{{ $metrics['pie_chart']['retained']['stroke_length'] }} {{ $metrics['pie_chart']['circumference'] }}"
                                    stroke-dashoffset="{{ $metrics['pie_chart']['retained']['stroke_offset'] }}" 
                                    class="transition-all duration-500" />
                        </svg>

                        <!-- Center Geometric Badge -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-stone-400 font-mono">Porsi Belanja</span>
                            <span class="text-2xl font-display font-bold text-stone-800">{{ $metrics['rasio_belanja_h1'] }}%</span>
                            <span class="text-[10px] font-medium text-stone-400">dari Omzet H-1</span>
                        </div>
                    </div>

                    <!-- Legend & Percentages in Soft Pastel Pills -->
                    <div class="w-full mt-5 space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-sky-50/70 border border-sky-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-sky-400 shrink-0"></span>
                                <span class="font-medium text-stone-800">{{ $metrics['pie_chart']['restock']['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-sky-700 font-mono">{{ $metrics['pie_chart']['restock']['percentage'] }}%</span>
                                <span class="text-[10px] text-stone-500 block font-mono">Rp {{ number_format($metrics['pie_chart']['restock']['amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-rose-50/70 border border-rose-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-400 shrink-0"></span>
                                <span class="font-medium text-stone-800">{{ $metrics['pie_chart']['operational']['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-rose-700 font-mono">{{ $metrics['pie_chart']['operational']['percentage'] }}%</span>
                                <span class="text-[10px] text-stone-500 block font-mono">Rp {{ number_format($metrics['pie_chart']['operational']['amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-400 shrink-0"></span>
                                <span class="font-medium text-stone-800">{{ $metrics['pie_chart']['retained']['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-emerald-700 font-mono">{{ $metrics['pie_chart']['retained']['percentage'] }}%</span>
                                <span class="text-[10px] text-stone-500 block font-mono">Rp {{ number_format($metrics['pie_chart']['retained']['amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Flow Explanation & Safe Practices -->
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <h3 class="text-sm font-display font-bold text-stone-900 tracking-tight">
                            Panduan Praktis Perputaran Uang Resto
                        </h3>
                    </div>

                    <div class="space-y-3 text-xs text-stone-600 leading-relaxed">
                        <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80">
                            <span class="font-bold text-stone-900 block mb-1">1. Sistem Belanja Dipotong dari Omzet H-1:</span>
                            <p>
                                Pembelian bahan segar harian (ayam, bumbu, sayuran, beras) diambil langsung dari kas fisik hasil penjualan hari kemarin (H-1). Cara ini melindungi kas operasional harian agar tidak mengalami defisit tak terduga.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80">
                            <span class="font-bold text-stone-900 block mb-1">2. Penarikan Dana Periode 1 Bulan:</span>
                            <p>
                                Seluruh dana non-tunai (QRIS & EDC) beserta sisa kas harian dibukukan dan ditarik per periode bulanan. Saldo kas tertahan saat ini untuk ditarik akhir bulan mencapai <strong class="text-emerald-700 font-mono">Rp {{ number_format($metrics['retained_monthly_fund'], 0, ',', '.') }}</strong>.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/70 text-emerald-900">
                            <span class="font-bold block mb-1">3. Sinkronisasi Otomatis dari Foto Struk AI:</span>
                            <p class="text-stone-600">
                                Setiap kali Anda memindai struk belanja pasar di menu <strong>Stok Bahan Baku</strong>, sistem secara otomatis mencatat pengeluaran ke Arus Kas dengan penanda <em>"Potong Omzet H-1"</em> sekaligus memperbarui stok dan HPP bahan baku Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status Kesehatan Kas Harian (Pastel Pill) -->
                <div class="rounded-2xl p-4 border flex items-center justify-between {{ $metrics['rasio_belanja_h1'] <= 40 ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : ($metrics['rasio_belanja_h1'] <= 70 ? 'bg-amber-50/80 border-amber-200 text-amber-900' : 'bg-rose-50/80 border-rose-200 text-rose-900') }}">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider font-mono block">Status Kesehatan Pengeluaran Harian</span>
                        <span class="text-xs font-semibold mt-0.5 block">
                            @if($metrics['rasio_belanja_h1'] <= 40)
                                Sangat Sehat — Rasio belanja bahan terkontrol di bawah 40% dari omzet H-1.
                            @elseif($metrics['rasio_belanja_h1'] <= 70)
                                Wajar — Pengeluaran bahan baku masih dalam rentang toleransi aman.
                            @else
                                Peringatan — Pengeluaran melebihi 70% omzet H-1! Periksa potensi pemborosan.
                            @endif
                        </span>
                    </div>
                    <div class="text-right pl-3 shrink-0">
                        <span class="text-[10px] text-stone-500 font-mono block">Sisa Kas Fisik H-1:</span>
                        <span class="text-sm font-mono font-bold text-stone-900">Rp {{ number_format($metrics['sisa_kas_h1'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUKU KAS & MUTASI KEUANGAN: TABEL & LEDGER -->
        <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-100 gap-3">
                <div>
                    <h3 class="text-sm font-display font-bold text-stone-900 tracking-tight">
                        Buku Kas & Riwayat Mutasi Keuangan
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Catatan seluruh transaksi masuk, belanja bahan baku restock, dan biaya operasional.</p>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('backoffice.cashflow.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari keterangan / no ref..." 
                           class="bg-stone-50 border border-stone-200 rounded-xl py-1.5 px-3 text-xs w-48 focus:ring-1 focus:ring-emerald-500">

                    <select name="type" onchange="this.form.submit()" class="bg-stone-50 border border-stone-200 rounded-xl py-1.5 px-2.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500">
                        <option value="">Semua Tipe</option>
                        <option value="INFLOW" {{ request('type') === 'INFLOW' ? 'selected' : '' }}>Uang Masuk (In)</option>
                        <option value="OUTFLOW" {{ request('type') === 'OUTFLOW' ? 'selected' : '' }}>Pengeluaran (Out)</option>
                    </select>

                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-stone-900 text-white font-bold text-xs transition">
                        Filter
                    </button>
                    @if(request('search') || request('type') || request('category'))
                        <a href="{{ route('backoffice.cashflow.index') }}" class="text-rose-600 text-xs font-bold hover:underline px-1">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Ledger Summary Strip: IN, OUT, SISA MUTASI -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 my-4 p-3 rounded-xl bg-stone-50 border border-stone-200/60">
                <div class="flex items-center justify-between sm:justify-start sm:gap-3">
                    <span class="text-xs text-stone-500 font-medium">Total Masuk Terdaftar:</span>
                    <span class="font-mono font-bold text-emerald-700 text-xs">+Rp {{ number_format($ledgerSummary['total_inflow'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-start sm:gap-3">
                    <span class="text-xs text-stone-500 font-medium">Total Keluar Terdaftar:</span>
                    <span class="font-mono font-bold text-rose-700 text-xs">-Rp {{ number_format($ledgerSummary['total_outflow'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-start sm:gap-3">
                    <span class="text-xs text-stone-500 font-medium">Selisih Mutasi (Net In - Out):</span>
                    <span class="font-mono font-bold text-xs {{ ($ledgerSummary['net_balance'] ?? 0) >= 0 ? 'text-indigo-900' : 'text-rose-600' }}">
                        Rp {{ number_format($ledgerSummary['net_balance'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Table in Pastel Soft Design -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50/70 text-stone-600 font-semibold text-[11px]">
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-2 text-center">Tipe</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                            <th class="py-2.5 px-2 text-center">Potong H-1?</th>
                            <th class="py-2.5 px-2 text-center">Bukti Struk</th>
                            <th class="py-2.5 px-3">Akun Kas</th>
                            <th class="py-2.5 px-3 text-right">Nominal</th>
                            <th class="py-2.5 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($mutations as $m)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="py-3 px-3 font-mono text-stone-600 whitespace-nowrap">
                                    {{ $m->transaction_date->format('d/m/Y') }}
                                </td>

                                <td class="py-3 px-2 text-center">
                                    @if($m->type === 'INFLOW')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100/70 text-emerald-800 border border-emerald-200/70">
                                            MASUK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100/70 text-rose-800 border border-rose-200/70">
                                            KELUAR
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3 px-3 font-bold text-[11px] text-stone-800">
                                    @switch($m->category)
                                        @case('RESTOCK_PURCHASE') Belanja Bahan (Restock) @break
                                        @case('OPERATIONAL') Biaya Operasional @break
                                        @case('PETTY_CASH') Kas Kecil Kasir @break
                                        @case('SALARY') Gaji Staf @break
                                        @case('CAPITAL_IN') Setoran Modal @break
                                        @case('SALES_CASH') Penjualan Tunai @break
                                        @case('SALES_NON_CASH') Penjualan QRIS @break
                                        @default {{ $m->category }}
                                    @endswitch
                                </td>

                                <td class="py-3 px-3 text-stone-800">
                                    <span class="font-medium">{{ $m->description }}</span>
                                    @if($m->user)
                                        <span class="text-[10px] text-stone-400 block">Oleh: {{ $m->user->name }}</span>
                                    @endif
                                </td>

                                <td class="py-3 px-2 text-center">
                                    @if($m->is_h_minus_one_deduction)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100/80 text-amber-900 border border-amber-200">
                                            YA (H-1)
                                        </span>
                                    @else
                                        <span class="text-[10px] text-stone-300 font-mono">-</span>
                                    @endif
                                </td>

                                <td class="py-3 px-2 text-center">
                                    @if($m->receipt_image_path)
                                        <button type="button" 
                                                @click="openReceiptPreview('{{ asset($m->receipt_image_path) }}')"
                                                class="px-2 py-0.5 rounded-lg border border-stone-200 bg-white text-[10px] font-semibold text-stone-700 hover:bg-stone-50 transition shadow-xs" 
                                                title="Lihat Foto Struk">
                                            📷 Struk
                                        </button>
                                    @else
                                        <span class="text-[10px] text-stone-300">-</span>
                                    @endif
                                </td>

                                <td class="py-3 px-3 text-[11px] text-stone-600">
                                    @switch($m->source_account)
                                        @case('CASH_DRAWER') Laci Kasir @break
                                        @case('PETTY_CASH') Kas Kecil @break
                                        @case('BANK_ACCOUNT') Rekening Bank @break
                                        @default {{ $m->source_account }}
                                    @endswitch
                                </td>

                                <td class="py-3 px-3 text-right font-mono font-bold text-sm whitespace-nowrap {{ $m->type === 'INFLOW' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $m->type === 'INFLOW' ? '+' : '-' }}Rp {{ number_format($m->amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3 px-2 text-right">
                                    <form method="POST" 
                                          action="{{ route('backoffice.cashflow.destroy', $m->id) }}" 
                                          onsubmit="return confirm('Hapus catatan arus kas ini?')" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded-md text-stone-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Mutasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-xs text-stone-400">
                                    Belum ada catatan mutasi kas pada tanggal ini. Klik "+ Pemasukan" atau "+ Pengeluaran" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mutations->hasPages())
                <div class="pt-4 border-t border-stone-100">
                    {{ $mutations->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: Catat Pengeluaran Baru (Pastel Modern) -->
        <div x-show="expenseModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-3 sm:p-4 backdrop-blur-xs overflow-y-auto" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-4 sm:p-6 shadow-xl border border-stone-200 max-h-[90vh] overflow-y-auto animate-in fade-in duration-150"
                 @click.outside="expenseModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <h3 class="text-sm font-display font-bold text-stone-900 tracking-tight">
                            Catat Pengeluaran Kas (Outflow)
                        </h3>
                    </div>
                    <button type="button" @click="expenseModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.cashflow.expense') }}" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Kategori Pengeluaran <span class="text-rose-500">*</span></label>
                            <select name="category" required class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full text-xs font-medium focus:ring-1 focus:ring-rose-500 focus:bg-white">
                                <option value="RESTOCK_PURCHASE">Belanja Bahan Baku (Restock)</option>
                                <option value="OPERATIONAL">Biaya Operasional Toko</option>
                                <option value="PETTY_CASH">Kas Kecil Harian</option>
                                <option value="SALARY">Gaji / Upah Karyawan</option>
                                <option value="RENT_UTILITIES">Listrik / Air / Sewa</option>
                                <option value="OWNER_WITHDRAWAL">Prive / Tarik Kas Owner</option>
                                <option value="OTHER">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" 
                                   name="amount" 
                                   min="1" 
                                   step="1" 
                                   placeholder="Contoh: 75000" 
                                   required 
                                   class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full font-mono text-sm focus:ring-1 focus:ring-rose-500 focus:bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Sumber Kas <span class="text-rose-500">*</span></label>
                            <select name="source_account" required class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full text-xs font-medium focus:ring-1 focus:ring-rose-500 focus:bg-white">
                                <option value="CASH_DRAWER">Laci Kasir (Cash Drawer)</option>
                                <option value="PETTY_CASH">Petty Cash Kasir</option>
                                <option value="BANK_ACCOUNT">Rekening Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                            <input type="date" 
                                   name="transaction_date" 
                                   value="{{ date('Y-m-d') }}" 
                                   required 
                                   class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full font-mono text-xs focus:ring-1 focus:ring-rose-500 focus:bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-stone-700 mb-1">Keterangan Pengeluaran <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="description" 
                               placeholder="Contoh: Beli bumbu pelengkap dan minyak goreng di pasar" 
                               required 
                               class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full text-xs focus:ring-1 focus:ring-rose-500 focus:bg-white">
                    </div>

                    <div>
                        <label class="block font-bold text-stone-700 mb-1">Upload Foto Nota / Struk (Opsional)</label>
                        <input type="file" 
                               name="receipt_image" 
                               accept="image/*" 
                               class="w-full text-xs text-stone-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer">
                    </div>

                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl flex items-center gap-2">
                        <input type="checkbox" 
                               name="is_h_minus_one_deduction" 
                               id="is_h_minus_one_exp" 
                               value="1" 
                               checked 
                               class="rounded border-stone-300 text-rose-600 focus:ring-rose-500">
                        <label for="is_h_minus_one_exp" class="font-medium text-stone-800 cursor-pointer text-xs">
                            Potong dari Omzet H-1 (Perputaran Uang Harian Resto)
                        </label>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-stone-100">
                        <button type="button" @click="expenseModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                            Simpan Pengeluaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Catat Pemasukan Baru (Pastel Modern) -->
        <div x-show="incomeModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-3 sm:p-4 backdrop-blur-xs overflow-y-auto" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-4 sm:p-6 shadow-xl border border-stone-200 max-h-[90vh] overflow-y-auto animate-in fade-in duration-150"
                 @click.outside="incomeModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-sm font-display font-bold text-stone-900 tracking-tight">
                            Catat Pemasukan Kas (Inflow)
                        </h3>
                    </div>
                    <button type="button" @click="incomeModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.cashflow.income') }}" class="space-y-3.5 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Kategori Pemasukan <span class="text-emerald-500">*</span></label>
                            <select name="category" required class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:bg-white">
                                <option value="CAPITAL_IN">Setoran Modal / Kas Tambahan</option>
                                <option value="SALES_CASH">Setoran Tunai Manual</option>
                                <option value="OTHER_INCOME">Pendapatan Lain-lain</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Nominal (Rp) <span class="text-emerald-500">*</span></label>
                            <input type="number" 
                                   name="amount" 
                                   min="1" 
                                   step="1" 
                                   placeholder="Contoh: 500000" 
                                   required 
                                   class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full font-mono text-sm focus:ring-1 focus:ring-emerald-500 focus:bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Masuk ke Rekening/Kas <span class="text-emerald-500">*</span></label>
                            <select name="source_account" required class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:bg-white">
                                <option value="CASH_DRAWER">Laci Kasir (Cash Drawer)</option>
                                <option value="PETTY_CASH">Petty Cash Kasir</option>
                                <option value="BANK_ACCOUNT">Rekening Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-700 mb-1">Tanggal Transaksi <span class="text-emerald-500">*</span></label>
                            <input type="date" 
                                   name="transaction_date" 
                                   value="{{ date('Y-m-d') }}" 
                                   required 
                                   class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full font-mono text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-stone-700 mb-1">Keterangan Pemasukan <span class="text-emerald-500">*</span></label>
                        <input type="text" 
                               name="description" 
                               placeholder="Contoh: Setoran modal awal operasional dari owner" 
                               required 
                               class="bg-stone-50 border border-stone-200 rounded-xl px-3 py-2 w-full text-xs focus:ring-1 focus:ring-emerald-500 focus:bg-white">
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-stone-100">
                        <button type="button" @click="incomeModalOpen = false" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                            Simpan Pemasukan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Preview Foto Struk Transaksi (Pastel Modern) -->
        <div x-show="receiptPreviewModal" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-xl w-full p-5 shadow-2xl border border-stone-200 animate-in fade-in duration-150"
                 @click.outside="receiptPreviewModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-3">
                    <span class="text-xs font-bold uppercase font-mono text-stone-700">Bukti Foto Struk Belanja</span>
                    <button type="button" @click="receiptPreviewModal = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>
                <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-stone-50 rounded-xl p-2 border border-stone-200">
                    <img :src="previewImageUrl" alt="Foto Struk" class="max-h-[70vh] object-contain rounded-lg">
                </div>
            </div>
        </div>

    </div>
</x-app-layout>