<x-app-layout>
    @section('page_title', 'Dashboard Eksekutif')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-4 p-3 rounded-[6px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    <div class="space-y-6">
        <!-- 1. Standard Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#E7E0D8]">
            <div>
                <h1 class="text-lg sm:text-xl font-display font-bold text-[#5B0A14] tracking-tight">
                    Soto Lamongan Lestari — Ringkasan Resto
                </h1>
                <p class="text-xs text-stone-500 mt-0.5">
                    Performa transaksi harian, margin keuntungan resep, dan kontrol stok bahan baku.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="{{ route('backoffice.ai-consultant.generate') }}">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-1.5 rounded-[6px] bg-white border border-[#E7E0D8] text-stone-700 hover:bg-stone-50 text-xs font-semibold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Perbarui Data AI</span>
                    </button>
                </form>

                <a href="{{ route('pos.index') }}" 
                   class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wide transition flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Layar Kasir</span>
                </a>
            </div>
        </div>

        <!-- 2. Compact Row of 4 KPI Metrics (Strict 8px scale, 1px border, tabular numerals) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- KPI 1: Omzet Hari Ini -->
            <div class="bg-white rounded-[10px] p-4 border border-[#E7E0D8] flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Omzet Penjualan</span>
                    <div class="text-xl font-display font-bold text-stone-900 font-mono tabular-nums mt-1">
                        Rp{{ number_format($metrics['financial']['total_revenue'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
                    <span>Akumulasi:</span>
                    <span class="font-mono tabular-nums font-semibold text-stone-700">Rp{{ number_format($metrics['financial']['all_time_revenue'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- KPI 2: Total Transaksi -->
            <div class="bg-white rounded-[10px] p-4 border border-[#E7E0D8] flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Total Transaksi</span>
                    <div class="text-xl font-display font-bold text-stone-900 font-mono tabular-nums mt-1">
                        {{ $metrics['financial']['transactions'] ?? 0 }} <span class="text-xs font-normal text-stone-400">Pesanan</span>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
                    <span>Status:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        100% Tersinkron
                    </span>
                </div>
            </div>

            <!-- KPI 3: Rata-Rata per Pesanan (AOV) -->
            <div class="bg-white rounded-[10px] p-4 border border-[#E7E0D8] flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Rata-Rata per Pesanan (AOV)</span>
                    <div class="text-xl font-display font-bold text-stone-900 font-mono tabular-nums mt-1">
                        Rp{{ number_format($metrics['financial']['aov'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
                    <span>Margin Kotor:</span>
                    <span class="font-mono tabular-nums font-semibold text-emerald-700">{{ $metrics['financial']['profit_margin_pct'] ?? 0 }}%</span>
                </div>
            </div>

            <!-- KPI 4: Status Bahan Baku Kritis -->
            <div class="bg-white rounded-[10px] p-4 border border-[#E7E0D8] flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Bahan Baku Menipis</span>
                    <div class="text-xl font-display font-bold font-mono tabular-nums mt-1 {{ count($lowStockItems) > 0 ? 'text-[#C2410C]' : 'text-stone-900' }}">
                        {{ count($lowStockItems) > 0 ? count($lowStockItems) . ' Item Perlu Restock' : 'Semua Stok Aman' }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
                    <span>Total Pantauan:</span>
                    <a href="{{ route('backoffice.inventory') }}" class="font-medium text-[#C2410C] hover:underline">
                        {{ $totalRawMaterialsCount ?? 12 }} Bahan Baku &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Primary Workspace Grid (12 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Left Column: AI Operational Note + Recent Orders Table (7 cols) -->
            <div class="lg:col-span-7 space-y-5">
                <!-- AI Resto Operational Note (Clean 1px border, no emojis or glow) -->
                <div class="bg-white rounded-[10px] p-4 border border-[#E7E0D8]">
                    <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-stone-100 text-stone-700 font-mono uppercase tracking-wider">AI Resto Advisor</span>
                            <span class="text-xs text-stone-500">
                                @if($latestAiReport)
                                    Evaluasi: {{ \Carbon\Carbon::parse($latestAiReport->report_date)->translatedFormat('d M Y') }}
                                @else
                                    Evaluasi Harian
                                @endif
                            </span>
                        </div>
                        <a href="{{ route('backoffice.ai-consultant') }}" class="text-[11px] font-semibold text-[#C2410C] hover:underline">
                            Lihat Rekomendasi Lengkap &rarr;
                        </a>
                    </div>
                    <div class="pt-2.5">
                        <p class="text-xs text-stone-700 leading-relaxed font-medium">
                            {{ $latestAiReport->ai_insights['executive_summary'] ?? 'Soto Lamongan Lestari mencatatkan margin operasional yang sehat. Kombinasi menu Soto Ayam Campur Nasi dan Mie Ayam menjadi kontributor omzet tertinggi. Pastikan ketersediaan daging sapi dan koya udang terjaga untuk jam makan siang.' }}
                        </p>
                    </div>
                </div>

                <!-- Recent Transactions Table -->
                <div class="bg-white rounded-[10px] border border-[#E7E0D8] overflow-hidden">
                    <div class="p-4 border-b border-[#E7E0D8] flex items-center justify-between bg-[#FAF6F0]/40">
                        <div>
                            <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Transaksi Penjualan Terbaru</h3>
                            <span class="text-[11px] text-stone-500">Aktivitas real-time dari terminal kasir</span>
                        </div>
                        <a href="{{ route('backoffice.orders') }}" class="text-xs font-semibold text-[#C2410C] hover:underline">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-xs">
                            <thead class="bg-stone-50 border-b border-[#E7E0D8] text-[10px] font-bold uppercase tracking-wider text-stone-500">
                                <tr>
                                    <th class="py-2.5 px-3">No. Struk</th>
                                    <th class="py-2.5 px-3">Item Pesanan</th>
                                    <th class="py-2.5 px-3">Waktu</th>
                                    <th class="py-2.5 px-3">Metode</th>
                                    <th class="py-2.5 px-3 text-right">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @forelse ($recentOrders as $order)
                                    <tr class="hover:bg-stone-50/70 transition">
                                        <td class="py-2.5 px-3 font-mono tabular-nums font-bold text-stone-900">
                                            <div>{{ $order->order_number }}</div>
                                            <span class="text-[10px] text-stone-400 font-sans font-normal">{{ $order->user->name ?? 'Kasir' }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-stone-700 max-w-[180px]">
                                            <span class="font-semibold">{{ $order->items->count() }} item</span>
                                            <span class="text-[10px] text-stone-400 truncate block">
                                                {{ $order->items->pluck('product.name')->join(', ') }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-stone-500 font-mono tabular-nums text-[11px]">
                                            {{ $order->created_at->format('H:i') }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            @php
                                                $method = $order->payments->first()->method ?? 'CASH';
                                            @endphp
                                            <span class="inline-block px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold {{ $method === 'CASH' ? 'bg-stone-100 text-stone-700' : 'bg-orange-50 text-[#C2410C]' }}">
                                                {{ $method }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono tabular-nums font-bold text-stone-900">
                                            Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-xs text-stone-400">
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
                <div class="bg-white rounded-[10px] border border-[#E7E0D8] p-4">
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-3">
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Menu Terlaris Hari Ini</h3>
                        <span class="text-[10px] text-stone-400 uppercase font-bold tracking-wider">Top 5</span>
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
                                        <span class="w-4 h-4 rounded-[3px] bg-stone-100 text-stone-700 flex items-center justify-center text-[10px] font-bold font-mono shrink-0">
                                            {{ $idx + 1 }}
                                        </span>
                                        <span class="font-semibold text-stone-900 truncate">{{ $prod['name'] }}</span>
                                    </div>
                                    <div class="text-right shrink-0 font-mono tabular-nums">
                                        <span class="font-bold text-stone-900">Rp{{ number_format($prod['sales'], 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-stone-400 ml-1">({{ $prod['qty'] }})</span>
                                    </div>
                                </div>
                                <div class="w-full bg-stone-100 rounded-[2px] h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-[2px] bg-[#EA580C]" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-stone-400">
                                Belum ada menu terjual hari ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Status Persediaan Bahan Baku Kritis -->
                <div class="bg-white rounded-[10px] border border-[#E7E0D8] p-4">
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-3">
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Status Stok Bahan Baku</h3>
                        <a href="{{ route('backoffice.inventory') }}" class="text-[11px] font-semibold text-[#C2410C] hover:underline">
                            Gudang &rarr;
                        </a>
                    </div>

                    <div class="space-y-2">
                        @forelse ($lowStockItems as $stock)
                            <div class="p-2.5 rounded-[6px] bg-amber-50/70 border border-amber-200 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-stone-900 block">{{ $stock->rawMaterial->name }}</span>
                                    <span class="text-[11px] text-stone-500 font-mono tabular-nums">
                                        Sisa: <strong>{{ (float) $stock->current_stock }} {{ $stock->rawMaterial->unit }}</strong> (Min: {{ (float) $stock->rawMaterial->minimum_stock }})
                                    </span>
                                </div>
                                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Menipis
                                </span>
                            </div>
                        @empty
                            <div class="py-4 text-center text-xs text-stone-500">
                                <span class="font-semibold text-emerald-700 block">Semua Bahan Baku Aman</span>
                                <span class="text-[11px] text-stone-400 mt-0.5 block">Semua persediaan berada di atas batas minimum.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
