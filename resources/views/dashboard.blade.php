<x-app-layout>
    @section('page_title', 'Dashboard Eksekutif')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    <div class="space-y-6">
        <!-- 1. Standard Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-200/80">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
                    Soto Lamongan Lestari — Ringkasan Resto & Bagi Hasil
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Performa transaksi harian, perbandingan omset H-1 dan sebulan, serta simulasi split bill bagi hasil.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <!-- Export Excel Split Bill & Omset -->
                <a href="{{ route('backoffice.dashboard.export-excel') }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 text-xs font-semibold shadow-xs transition flex items-center gap-1.5"
                   title="Unduh Laporan Rekap Omset & Split Bill Excel (.xls)">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel Split Bill</span>
                </a>

                <!-- Restock Bahan Cepat -->
                <a href="{{ route('backoffice.restock') }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5"
                   title="Restock Bahan Baku & Pembelian Pasar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>+ Restock Bahan</span>
                </a>

                <!-- Buka Layar Kasir -->
                <a href="{{ route('pos.index') }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs tracking-wide shadow-xs transition flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Layar Kasir</span>
                </a>
            </div>
        </div>

        <!-- 2. Row of 4 Core KPI Metrics: Hari Ini, Kemarin H-1, Bulan Ini, dan Split Bill -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- KPI 1: Omset Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Omset Hari Ini</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Penjualan Berjalan"></span>
                    </div>
                    <div class="text-xl font-bold text-slate-800 font-mono tabular-nums mt-1">
                        Rp{{ number_format($metrics['financial']['total_revenue'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>{{ $metrics['financial']['transactions'] ?? 0 }} Transaksi</span>
                    <span class="font-mono tabular-nums font-semibold text-emerald-700">Margin {{ $metrics['financial']['profit_margin_pct'] ?? 0 }}%</span>
                </div>
            </div>

            <!-- KPI 2: Omset Kemarin (H-1) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Omset Kemarin (H-1)</span>
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded-lg bg-slate-100 text-slate-600">H-1</span>
                    </div>
                    <div class="text-xl font-bold text-slate-800 font-mono tabular-nums mt-1">
                        Rp{{ number_format($yesterdayRevenue ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">{{ $yesterdayOrdersCount ?? 0 }} Transaksi</span>
                    @if (($pctYesterday ?? 0) >= 0)
                        <span class="font-semibold text-emerald-700 font-mono tabular-nums" title="Pertumbuhan hari ini vs kemarin">
                            +{{ $pctYesterday }}% vs H-1
                        </span>
                    @else
                        <span class="font-semibold text-rose-600 font-mono tabular-nums" title="Perubahan hari ini vs kemarin">
                            {{ $pctYesterday }}% vs H-1
                        </span>
                    @endif
                </div>
            </div>

            <!-- KPI 3: Omset Sebulan & Belanja Restock (MTD) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Omset Sebulan (MTD)</span>
                        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700">{{ \Carbon\Carbon::now()->translatedFormat('M Y') }}</span>
                    </div>
                    <div class="text-xl font-bold text-slate-800 font-mono tabular-nums mt-1">
                        Rp{{ number_format($monthRevenue ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-rose-600 font-semibold" title="Total Belanja Restock Bahan Bulan Ini">Restock: -Rp{{ number_format($monthRestockExpense ?? 0, 0, ',', '.') }}</span>
                    <span class="font-mono tabular-nums font-bold text-emerald-700" title="Omset Bersih setelah dipotong belanja bahan">Bersih: Rp{{ number_format($monthNetRevenue ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- KPI 4: Estimasi Bagi Hasil 50% Bulan Ini (Split Bill) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bagi Hasil 50% (Omset Bersih)</span>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700">50 : 50</span>
                    </div>
                    <div class="text-xl font-bold text-emerald-700 font-mono tabular-nums mt-1">
                        Rp{{ number_format($splitBillMonth['share_pengelola'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Kemarin H-1:</span>
                    <span class="font-mono tabular-nums font-semibold text-slate-700">Rp{{ number_format($splitBillYesterday['share_pengelola'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Dedicated Panel: Rekap Pembagian Hasil (Split Bill Usaha) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h2 class="text-sm font-bold text-slate-800">
                            Perhitungan Bagi Hasil (Split Bill Hasil Usaha Resto)
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Alokasi pembagian hasil transparan: Omset kotor dipotong biaya belanja restock bahan dan operasional untuk menghasilkan laba bersih.
                    </p>
                </div>

                <a href="{{ route('backoffice.dashboard.export-excel') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Rekap Split Bill (.xls)</span>
                </a>
            </div>

            <!-- 2-Box Split Grid: Kemarin vs Bulan Berjalan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Box A: Bagi Hasil Kemarin (H-1) -->
                <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-200/70 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">1. Bagi Hasil Kemarin (H-1)</span>
                        <span class="text-[10px] font-mono text-slate-400">{{ \Carbon\Carbon::yesterday()->translatedFormat('d F Y') }}</span>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Total Omset Kotor H-1:</span>
                            <span class="font-bold text-slate-800 font-mono tabular-nums">Rp{{ number_format($yesterdayRevenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-500">
                            <span>Belanja Restock Kemarin:</span>
                            <span class="font-mono tabular-nums text-rose-600">-Rp{{ number_format($splitBillYesterday['restock_expense'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-slate-100 font-semibold">
                            <span class="text-slate-700">Omset Bersih H-1:</span>
                            <span class="text-emerald-700 font-mono tabular-nums">Rp{{ number_format($splitBillYesterday['net_revenue'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-200/80">
                            <span class="text-[10px] font-semibold text-emerald-800 block">Porsi Pengelola (50%)</span>
                            <span class="text-sm font-bold text-emerald-700 font-mono tabular-nums block mt-1">
                                Rp{{ number_format($splitBillYesterday['share_pengelola'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Dari Omset Kotor: Rp{{ number_format($splitBillYesterday['gross_share_pengelola'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/80 border border-indigo-200/80">
                            <span class="text-[10px] font-semibold text-indigo-800 block">Porsi Pemodal / Owner (50%)</span>
                            <span class="text-sm font-bold text-indigo-700 font-mono tabular-nums block mt-1">
                                Rp{{ number_format($splitBillYesterday['share_investor'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Dari Omset Kotor: Rp{{ number_format($splitBillYesterday['gross_share_investor'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Box B: Bagi Hasil 1 Bulan Ini (MTD) -->
                <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-200/70 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">2. Bagi Hasil Bulan Ini (MTD)</span>
                        <span class="text-[10px] font-mono text-slate-400">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-slate-200/80 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Omset Kotor Bulan Ini:</span>
                            <span class="font-bold text-slate-800 font-mono tabular-nums">Rp{{ number_format($monthRevenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Total Belanja Restock Bahan:</span>
                            <span class="font-mono tabular-nums text-rose-600">-Rp{{ number_format($monthRestockExpense ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Biaya Operasional Toko:</span>
                            <span class="font-mono tabular-nums text-rose-600">-Rp{{ number_format($monthOperationalExpense ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-100 font-semibold">
                            <span class="text-slate-700">Omset Bersih Siap Bagi:</span>
                            <span class="text-emerald-700 font-mono tabular-nums">Rp{{ number_format($monthNetRevenue ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-200/80">
                            <span class="text-[10px] font-semibold text-emerald-800 block">Split 50% Pengelola (Net)</span>
                            <span class="text-sm font-bold text-emerald-700 font-mono tabular-nums block mt-1">
                                Rp{{ number_format($splitBillMonth['share_pengelola'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Dari Kotor: Rp{{ number_format($splitBillMonth['gross_share_pengelola'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/80 border border-indigo-200/80">
                            <span class="text-[10px] font-semibold text-indigo-800 block">Split 50% Pemodal (Net)</span>
                            <span class="text-sm font-bold text-indigo-700 font-mono tabular-nums block mt-1">
                                Rp{{ number_format($splitBillMonth['share_investor'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Dari Kotor: Rp{{ number_format($splitBillMonth['gross_share_investor'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Primary Workspace Grid (12 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Left Column: Aktivitas Restock & Recent Orders Table (7 cols) -->
            <div class="lg:col-span-7 space-y-5">
                <!-- Aktivitas Restock Bahan Terkini (Pengganti AI) -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-mono uppercase tracking-wider">Restock Bahan</span>
                            <span class="text-xs text-slate-500">
                                Total Belanja Bulan Ini: <strong class="text-slate-800 font-mono">Rp{{ number_format($monthRestockExpense ?? 0, 0, ',', '.') }}</strong>
                            </span>
                        </div>
                        <a href="{{ route('backoffice.restock') }}" class="text-[11px] font-semibold text-emerald-700 hover:underline flex items-center gap-1">
                            <span>+ Restock Bahan</span> &rarr;
                        </a>
                    </div>
                    <div class="pt-3">
                        @if(!empty($recentRestocks) && $recentRestocks->isNotEmpty())
                            <div class="divide-y divide-slate-100">
                                @foreach($recentRestocks as $movement)
                                    <div class="py-2 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]">
                                                📦
                                            </span>
                                            <div>
                                                <span class="font-bold text-slate-800 block">{{ $movement->outletStock?->rawMaterial?->name ?? 'Bahan Baku' }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $movement->notes }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-emerald-700 font-mono">+{{ number_format($movement->quantity_delta, 0, ',', '.') }} {{ $movement->outletStock?->rawMaterial?->unit ?? 'gr' }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $movement->created_at?->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-slate-400 text-xs">
                                Belum ada riwayat restock bahan bulan ini.
                                <a href="{{ route('backoffice.restock') }}" class="text-emerald-600 font-semibold hover:underline block mt-1">Lakukan Restock Sekarang</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Grafik Jam Sibuk Resto (Peak Hours Distribution) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 mb-3">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Grafik Jam Sibuk Resto (Peak Hours)</h3>
                            <span class="text-[11px] text-slate-500">Distribusi omzet per jam operasional hari ini (08:00 - 22:00)</span>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">Real-Time</span>
                    </div>

                    @php
                        $maxHourlySales = !empty($hourlyDistribution) ? max($hourlyDistribution) : 1;
                        if ($maxHourlySales <= 0) $maxHourlySales = 1;
                    @endphp

                    <div class="flex items-end gap-1.5 h-32 pt-4 px-1 pb-1 border-b border-slate-200">
                        @foreach($hourlyDistribution as $hour => $sales)
                            @php
                                $barHeightPct = max(6, round(($sales / $maxHourlySales) * 100));
                                $isPeak = $sales > 0 && $sales >= ($maxHourlySales * 0.7);
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                                <div class="hidden group-hover:block absolute bottom-full mb-1 z-20 bg-slate-900 text-white text-[10px] py-1 px-1.5 rounded-lg whitespace-nowrap shadow-xs pointer-events-none font-mono">
                                    <span class="font-bold">{{ $hour }}</span>: Rp{{ number_format($sales, 0, ',', '.') }}
                                </div>
                                <div class="w-full rounded-t-lg transition-all duration-300 {{ $isPeak ? 'bg-amber-500' : ($sales > 0 ? 'bg-emerald-600' : 'bg-slate-200') }}"
                                     style="height: {{ $barHeightPct }}%"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center text-[9px] text-slate-400 font-mono mt-1.5 px-0.5">
                        <span>08:00</span>
                        <span>12:00 (Makan Siang)</span>
                        <span>18:00 (Makan Malam)</span>
                        <span>22:00</span>
                    </div>
                </div>

                <!-- Recent Transactions Table -->
                <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Transaksi Penjualan Terbaru</h3>
                            <span class="text-[11px] text-slate-500">Aktivitas real-time dari terminal kasir</span>
                        </div>
                        <a href="{{ route('backoffice.orders') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="py-2.5 px-3">No. Struk</th>
                                    <th class="py-2.5 px-3">Item Pesanan</th>
                                    <th class="py-2.5 px-3">Waktu</th>
                                    <th class="py-2.5 px-3">Metode</th>
                                    <th class="py-2.5 px-3 text-right">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentOrders as $order)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-2.5 px-3 font-mono tabular-nums font-bold text-slate-800">
                                            <div>{{ $order->order_number }}</div>
                                            <span class="text-[10px] text-slate-400 font-sans font-normal">{{ $order->user->name ?? 'Kasir' }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-700 max-w-[180px]">
                                            <span class="font-semibold">{{ $order->items->count() }} item</span>
                                            <span class="text-[10px] text-slate-400 truncate block">
                                                {{ $order->items->pluck('product.name')->join(', ') }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-500 font-mono tabular-nums text-[11px]">
                                            {{ $order->created_at->format('H:i') }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            @php
                                                $method = $order->payments->first()->method ?? 'CASH';
                                            @endphp
                                            <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-semibold {{ $method === 'CASH' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                                {{ $method }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono tabular-nums font-bold text-emerald-700">
                                            Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                            Belum ada transaksi hari ini. Buka kasir untuk memulai.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Top Selling Menu + Raw Material Alerts (5 cols) -->
            <div class="lg:col-span-5 space-y-5">
                <!-- Top 5 Menu Terlaris -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Menu Terlaris Hari Ini</h3>
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Top 5</span>
                    </div>

                    @php
                        $maxQty = !empty($metrics['top_selling']) ? max(array_column($metrics['top_selling'], 'qty')) : 1;
                    @endphp

                    <div class="space-y-3">
                        @forelse ($metrics['top_selling'] as $idx => $prod)
                            @php
                                $percent = $maxQty > 0 ? round(($prod['qty'] / $maxQty) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-4 h-4 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center text-[10px] font-bold font-mono shrink-0">
                                            {{ $idx + 1 }}
                                        </span>
                                        <span class="font-semibold text-slate-800 truncate">{{ $prod['name'] }}</span>
                                    </div>
                                    <div class="text-right shrink-0 font-mono tabular-nums">
                                        <span class="font-bold text-slate-800">Rp{{ number_format($prod['sales'], 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-slate-400 ml-1">({{ $prod['qty'] }})</span>
                                    </div>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-slate-400">
                                Belum ada menu terjual hari ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Status Persediaan Bahan Baku Kritis -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Status Stok Bahan Baku</h3>
                        <a href="{{ route('backoffice.inventory') }}" class="text-[11px] font-semibold text-emerald-700 hover:underline">
                            Gudang &rarr;
                        </a>
                    </div>

                    <div class="space-y-2">
                        @forelse ($lowStockItems as $stock)
                            <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-semibold text-slate-800 block">{{ $stock->rawMaterial->name }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono tabular-nums">
                                        Sisa: <strong>{{ (float) $stock->current_stock }} {{ $stock->rawMaterial->unit }}</strong> (Min: {{ (float) $stock->rawMaterial->minimum_stock }})
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    Menipis
                                </span>
                            </div>
                        @empty
                            <div class="py-4 text-center text-xs text-slate-500">
                                <span class="font-semibold text-emerald-700 block">Semua Bahan Baku Aman</span>
                                <span class="text-[11px] text-slate-400 mt-0.5 block">Semua persediaan berada di atas batas minimum.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
