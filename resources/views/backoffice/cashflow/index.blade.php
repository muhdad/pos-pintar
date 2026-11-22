<x-app-layout>
    @section('page_title', 'ARUS KAS & PERPUTARAN DANA (BAUHAUS)')

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

        <!-- Top Header & Date Selector Strip -->
        <div class="bauhaus-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-3 h-3 bg-[#1D4ED8] border border-black"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-[#1D4ED8] font-mono">SISTEM KEUANGAN & LIKUIDITAS</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-display font-black text-[#121212] uppercase tracking-wide">
                    Arus Kas & Perputaran Omzet H-1
                </h1>
                <p class="text-xs text-stone-600 font-medium mt-0.5">
                    Pembelanjaan harian dipotong dari omzet H-1. Total omzet tertahan diakumulasi untuk jadwal penarikan 1 bulan sekali.
                </p>
            </div>

            <!-- Controls: Date Picker & Quick Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('backoffice.cashflow.index') }}" class="flex items-center gap-1.5">
                    <label class="text-[10px] font-black uppercase font-mono text-stone-600">Tanggal:</label>
                    <input type="date" 
                           name="date" 
                           value="{{ request('date', $selectedDate) }}" 
                           onchange="this.form.submit()" 
                           class="bauhaus-input text-xs font-mono py-1 px-2.5">
                </form>

                <button type="button" 
                        @click="expenseModalOpen = true"
                        class="bauhaus-btn-red">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>+ Pengeluaran</span>
                </button>

                <button type="button" 
                        @click="incomeModalOpen = true"
                        class="bauhaus-btn-blue">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>+ Pemasukan</span>
                </button>

                <a href="{{ route('backoffice.inventory') }}" class="bauhaus-btn-yellow">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                    <span>Foto Struk AI</span>
                </a>
            </div>
        </div>

        <!-- 4-Col Key Metric Bauhaus Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Omzet Kemarin (H-1) -->
            <div class="bauhaus-card p-4 bg-white border-2 border-[#121212] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full bg-[#1D4ED8]"></div>
                <div class="pl-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 font-mono">OMZET H-1 (KEMARIN)</span>
                        <span class="bauhaus-badge bg-blue-50 text-[#1D4ED8]">{{ $metrics['yesterday_date'] }}</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-[#121212] tracking-tight">
                        Rp {{ number_format($metrics['omzet_h1'], 0, ',', '.') }}
                    </div>
                    <div class="mt-2 text-[10px] font-mono text-stone-600 space-y-0.5 border-t border-stone-200 pt-1.5">
                        <div class="flex justify-between">
                            <span>Tunai:</span>
                            <span class="font-bold">Rp {{ number_format($metrics['omzet_h1_cash'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>QRIS / EDC:</span>
                            <span class="font-bold">Rp {{ number_format($metrics['omzet_h1_non_cash'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Pembelanjaan Hari Ini (Dipotong dari H-1) -->
            <div class="bauhaus-card p-4 bg-white border-2 border-[#121212] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full bg-[#DC2626]"></div>
                <div class="pl-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 font-mono">BELANJA HARI INI (H)</span>
                        @php
                            $rasio = $metrics['rasio_belanja_h1'];
                            $rasioBadge = $rasio > 75 ? 'bg-red-100 text-red-900 border-red-600' : ($rasio > 45 ? 'bg-amber-100 text-amber-900 border-amber-600' : 'bg-emerald-100 text-emerald-900 border-emerald-600');
                        @endphp
                        <span class="bauhaus-badge {{ $rasioBadge }}">
                            {{ $rasio }}% DARI H-1
                        </span>
                    </div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-[#DC2626] tracking-tight">
                        Rp {{ number_format($metrics['total_today_expense'], 0, ',', '.') }}
                    </div>
                    <div class="mt-2 text-[10px] font-mono text-stone-600 space-y-0.5 border-t border-stone-200 pt-1.5">
                        <div class="flex justify-between">
                            <span>Bahan Baku (Restock):</span>
                            <span class="font-bold">Rp {{ number_format($metrics['today_restock'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Operasional/Kas Kecil:</span>
                            <span class="font-bold">Rp {{ number_format($metrics['today_operational'] + $metrics['today_other_expense'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Sisa Kas Operasional H-1 -->
            <div class="bauhaus-card p-4 bg-white border-2 border-[#121212] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full bg-[#FACC15]"></div>
                <div class="pl-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-stone-500 font-mono">SISA KAS OMZET H-1</span>
                        <span class="bauhaus-badge {{ $metrics['defisit_h1'] > 0 ? 'bg-red-600 text-white' : 'bg-[#FACC15] text-[#121212]' }}">
                            {{ $metrics['defisit_h1'] > 0 ? 'DEFISIT' : 'SURPLUS' }}
                        </span>
                    </div>
                    @if($metrics['defisit_h1'] > 0)
                        <div class="text-xl sm:text-2xl font-mono font-black text-red-600 tracking-tight">
                            -Rp {{ number_format($metrics['defisit_h1'], 0, ',', '.') }}
                        </div>
                        <p class="text-[10px] text-red-700 font-bold mt-2">
                            ⚠️ Belanja melebihi omzet kemarin! Menggunakan kas cadangan.
                        </p>
                    @else
                        <div class="text-xl sm:text-2xl font-mono font-black text-[#121212] tracking-tight">
                            Rp {{ number_format($metrics['sisa_kas_h1'], 0, ',', '.') }}
                        </div>
                        <p class="text-[10px] text-stone-600 mt-2 font-mono">
                            Omzet Hari Ini (Berjalan): <strong class="text-emerald-700">Rp {{ number_format($metrics['omzet_hari_ini'], 0, ',', '.') }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Card 4: Akumulasi Omzet Tertahan (Penarikan 1 Bulan Sekali) -->
            <div class="bauhaus-card p-4 bg-[#121212] text-white border-2 border-[#121212] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-2 h-full bg-[#FACC15]"></div>
                <div class="pl-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#FACC15] font-mono">PENARIKAN 1 BULAN</span>
                        <span class="bauhaus-badge bg-[#FACC15] text-[#121212] border-white">BULAN INI</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-white tracking-tight">
                        Rp {{ number_format($metrics['retained_monthly_fund'], 0, ',', '.') }}
                    </div>
                    <div class="mt-2 text-[10px] font-mono text-stone-300 space-y-0.5 border-t border-stone-800 pt-1.5">
                        <div class="flex justify-between">
                            <span>Omzet Bulan Berjalan:</span>
                            <span class="text-emerald-400 font-bold">Rp {{ number_format($metrics['month_revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Belanja Bulan Ini:</span>
                            <span class="text-red-400 font-bold">-Rp {{ number_format($metrics['month_expense'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bauhaus Interactive SVG Pie Chart (Perputaran Uang) & Financial Flow -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: SVG Pie Chart -->
            <div class="lg:col-span-5 bauhaus-card p-5 bg-white">
                <div class="flex items-center justify-between border-b-2 border-stone-100 pb-3 mb-4">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 bg-[#DC2626] border border-black"></span>
                            <h2 class="text-sm font-display font-black text-[#121212] uppercase tracking-wide">
                                Diagram Perputaran Kas (Pie Chart)
                            </h2>
                        </div>
                        <span class="text-[10px] text-stone-500 font-mono">Distribusi Alokasi Dana Omzet H-1</span>
                    </div>
                    <span class="bauhaus-badge bg-stone-100 text-stone-800 font-mono">100% BASIS</span>
                </div>

                <div class="flex flex-col items-center justify-center py-2">
                    <!-- SVG Donut / Pie Graphic -->
                    <div class="relative w-48 h-48 sm:w-52 sm:h-52">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                            <!-- Background Circle Track -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="#E4E4E7" 
                                    stroke-width="20" />

                            <!-- Slice 1: Belanja Bahan Baku (Cobalt Blue #1D4ED8) -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="{{ $metrics['pie_chart']['restock']['color'] }}" 
                                    stroke-width="20"
                                    stroke-dasharray="{{ $metrics['pie_chart']['restock']['stroke_length'] }} {{ $metrics['pie_chart']['circumference'] }}"
                                    stroke-dashoffset="{{ $metrics['pie_chart']['restock']['stroke_offset'] }}" 
                                    class="transition-all duration-500" />

                            <!-- Slice 2: Biaya Operasional (Crimson Red #DC2626) -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="{{ $metrics['pie_chart']['operational']['color'] }}" 
                                    stroke-width="20"
                                    stroke-dasharray="{{ $metrics['pie_chart']['operational']['stroke_length'] }} {{ $metrics['pie_chart']['circumference'] }}"
                                    stroke-dashoffset="{{ $metrics['pie_chart']['operational']['stroke_offset'] }}" 
                                    class="transition-all duration-500" />

                            <!-- Slice 3: Sisa Kas H-1 / Laba Tertahan (Canary Yellow #FACC15) -->
                            <circle cx="60" cy="60" r="50" 
                                    fill="transparent" 
                                    stroke="{{ $metrics['pie_chart']['retained']['color'] }}" 
                                    stroke-width="20"
                                    stroke-dasharray="{{ $metrics['pie_chart']['retained']['stroke_length'] }} {{ $metrics['pie_chart']['circumference'] }}"
                                    stroke-dashoffset="{{ $metrics['pie_chart']['retained']['stroke_offset'] }}" 
                                    class="transition-all duration-500" />
                        </svg>

                        <!-- Center Geometric Badge -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-[9px] font-black uppercase tracking-wider text-stone-500 font-mono">RASIO BELANJA</span>
                            <span class="text-2xl font-display font-black text-[#121212]">{{ $metrics['rasio_belanja_h1'] }}%</span>
                            <span class="text-[9px] font-bold text-stone-400 font-mono">DARI OMZET H-1</span>
                        </div>
                    </div>

                    <!-- Legend & Percentages -->
                    <div class="w-full mt-4 space-y-2 font-mono text-xs">
                        <div class="flex items-center justify-between p-2 border-2 border-[#121212] bg-blue-50/50">
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 bg-[#1D4ED8] border border-black shrink-0"></span>
                                <span class="font-bold text-stone-800">{{ $metrics['pie_chart']['restock']['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-[#1D4ED8]">{{ $metrics['pie_chart']['restock']['percentage'] }}%</span>
                                <span class="text-[10px] text-stone-500 block">Rp {{ number_format($metrics['pie_chart']['restock']['amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2 border-2 border-[#121212] bg-red-50/50">
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 bg-[#DC2626] border border-black shrink-0"></span>
                                <span class="font-bold text-stone-800">{{ $metrics['pie_chart']['operational']['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-[#DC2626]">{{ $metrics['pie_chart']['operational']['percentage'] }}%</span>
                                <span class="text-[10px] text-stone-500 block">Rp {{ number_format($metrics['pie_chart']['operational']['amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2 border-2 border-[#121212] bg-amber-50/50">
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 bg-[#FACC15] border border-black shrink-0"></span>
                                <span class="font-bold text-stone-800">{{ $metrics['pie_chart']['retained']['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-amber-800">{{ $metrics['pie_chart']['retained']['percentage'] }}%</span>
                                <span class="text-[10px] text-stone-500 block">Rp {{ number_format($metrics['pie_chart']['retained']['amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Flow Explanation & Liquidity Guide -->
            <div class="lg:col-span-7 space-y-4">
                <!-- Liquidity Principle Card -->
                <div class="bauhaus-card p-5 bg-white">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-3 h-3 bg-[#FACC15] border border-black"></span>
                        <h3 class="text-sm font-display font-black text-[#121212] uppercase tracking-wide">
                            Aturan Perputaran Uang Restoran
                        </h3>
                    </div>

                    <div class="space-y-3 text-xs text-stone-700 leading-relaxed">
                        <div class="p-3 bg-[#F6F4ED] border-2 border-stone-800">
                            <span class="font-black text-[#121212] uppercase font-mono block mb-1">1. Mekanisme Belanja Dipotong dari Omzet H-1:</span>
                            <p>
                                Kasir dan staf dapur membeli bahan segar (daging ayam, sayur, bihun, bumbu) menggunakan kas fisik hasil penjualan hari kemarin (H-1). Hal ini menjaga agar modal operasional harian tidak defisit dan tidak mengganggu laba bersih bulan berjalan.
                            </p>
                        </div>

                        <div class="p-3 bg-[#F6F4ED] border-2 border-stone-800">
                            <span class="font-black text-[#121212] uppercase font-mono block mb-1">2. Penarikan Dana Ditarik 1 Bulan Sekali:</span>
                            <p>
                                Seluruh dana dari pembayaran non-tunai (QRIS, EDC, Transfer) dan sisa kas harian dibukukan dan ditarik per periode bulanan. 
                                Saldo tertahan saat ini untuk ditarik pada akhir bulan adalah <strong class="text-emerald-800 font-mono">Rp {{ number_format($metrics['retained_monthly_fund'], 0, ',', '.') }}</strong>.
                            </p>
                        </div>

                        <div class="p-3 bg-blue-50 border-2 border-[#1D4ED8] text-[#1D4ED8]">
                            <span class="font-black uppercase font-mono block mb-1">3. Sinkronisasi Otomatis via Foto Struk AI:</span>
                            <p class="text-stone-700">
                                Setiap kali Anda memindai struk belanja pasar di menu <strong>Stok Bahan Baku</strong>, sistem secara otomatis mencatat pengeluaran ke Arus Kas dengan penanda <em>"Potong Omzet H-1"</em> dan langsung memperbarui stok fisik serta HPP (moving average cost) bahan baku!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Balance Health Pill -->
                <div class="bauhaus-card p-4 bg-[#121212] text-white flex items-center justify-between">
                    <div>
                        <span class="text-[9px] font-black text-[#FACC15] uppercase tracking-widest font-mono block">STATUS KESEHATAN KAS HARIAN</span>
                        <span class="text-sm font-bold text-white">
                            @if($metrics['rasio_belanja_h1'] <= 40)
                                🟢 Sangat Sehat — Rasio belanja bahan di bawah 40% dari omzet H-1.
                            @elseif($metrics['rasio_belanja_h1'] <= 70)
                                🟡 Wajar — Pengeluaran bahan baku dalam batas toleransi normal.
                            @else
                                🔴 Peringatan — Pengeluaran melebihi 70% omzet H-1! Periksa pemborosan.
                            @endif
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-stone-400 font-mono block">Sisa Kas Fisik:</span>
                        <span class="text-sm font-mono font-black text-[#FACC15]">Rp {{ number_format($metrics['sisa_kas_h1'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Mutation Ledger Table -->
        <div class="bauhaus-card p-5 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b-2 border-stone-900 gap-3">
                <div>
                    <h3 class="text-sm font-display font-black text-[#121212] uppercase tracking-wider">
                        Buku Kas & Riwayat Mutasi Keuangan
                    </h3>
                    <p class="text-[11px] text-stone-500">Catatan lengkap mutasi masuk, belanja bahan restock, dan operasional.</p>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('backoffice.cashflow.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari keterangan / no ref..." 
                           class="bauhaus-input py-1 px-2.5 text-xs w-48">

                    <select name="type" onchange="this.form.submit()" class="bauhaus-input py-1 px-2 text-xs font-mono">
                        <option value="">Semua Tipe</option>
                        <option value="OUTFLOW" {{ request('type') === 'OUTFLOW' ? 'selected' : '' }}>Pengeluaran (Outflow)</option>
                        <option value="INFLOW" {{ request('type') === 'INFLOW' ? 'selected' : '' }}>Pemasukan (Inflow)</option>
                    </select>

                    <button type="submit" class="bauhaus-btn-dark py-1 px-2.5 text-xs">Filter</button>
                    @if(request('search') || request('type') || request('category'))
                        <a href="{{ route('backoffice.cashflow.index') }}" class="text-red-600 text-xs font-bold hover:underline">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead>
                        <tr class="border-b-2 border-[#121212] bg-[#F6F4ED] text-[#121212] font-mono font-black uppercase text-[10px] tracking-wider">
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-2">Tipe</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                            <th class="py-2.5 px-2 text-center">Potong H-1?</th>
                            <th class="py-2.5 px-2 text-center">Bukti Struk</th>
                            <th class="py-2.5 px-3">Akun Kas</th>
                            <th class="py-2.5 px-3 text-right">Nominal</th>
                            <th class="py-2.5 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-stone-100">
                        @forelse($mutations as $m)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="py-3 px-3 font-mono text-stone-700 whitespace-nowrap">
                                    {{ $m->transaction_date->format('d/m/Y') }}
                                </td>

                                <td class="py-3 px-2">
                                    @if($m->type === 'INFLOW')
                                        <span class="bauhaus-badge bg-emerald-100 text-emerald-900 border-emerald-800">MASUK</span>
                                    @else
                                        <span class="bauhaus-badge bg-red-100 text-red-900 border-red-800">KELUAR</span>
                                    @endif
                                </td>

                                <td class="py-3 px-3 font-mono font-bold text-[11px] text-stone-800">
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

                                <td class="py-3 px-3 text-stone-900 font-medium">
                                    {{ $m->description }}
                                    @if($m->user)
                                        <span class="text-[10px] text-stone-400 font-mono block">Oleh: {{ $m->user->name }}</span>
                                    @endif
                                </td>

                                <td class="py-3 px-2 text-center">
                                    @if($m->is_h_minus_one_deduction)
                                        <span class="bauhaus-badge bg-[#FACC15] text-[#121212]">YA (H-1)</span>
                                    @else
                                        <span class="text-[10px] text-stone-400 font-mono">-</span>
                                    @endif
                                </td>

                                <td class="py-3 px-2 text-center">
                                    @if($m->receipt_image_path)
                                        <button type="button" 
                                                @click="openReceiptPreview('{{ asset($m->receipt_image_path) }}')"
                                                class="px-2 py-0.5 border border-stone-800 bg-stone-100 text-[10px] font-mono font-bold hover:bg-stone-200" 
                                                title="Lihat Foto Struk">
                                            📷 Lihat
                                        </button>
                                    @else
                                        <span class="text-[10px] text-stone-300 font-mono">Tidak ada</span>
                                    @endif
                                </td>

                                <td class="py-3 px-3 font-mono text-[11px] text-stone-600">
                                    @switch($m->source_account)
                                        @case('CASH_DRAWER') Laci Kasir @break
                                        @case('PETTY_CASH') Kas Kecil @break
                                        @case('BANK_ACCOUNT') Rekening Bank @break
                                        @default {{ $m->source_account }}
                                    @endswitch
                                </td>

                                <td class="py-3 px-3 text-right font-mono font-bold text-sm whitespace-nowrap {{ $m->type === 'INFLOW' ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $m->type === 'INFLOW' ? '+' : '-' }}Rp {{ number_format($m->amount, 0, ',', '.') }}
                                </td>

                                <td class="py-3 px-2 text-right">
                                    <form method="POST" 
                                          action="{{ route('backoffice.cashflow.destroy', $m->id) }}" 
                                          onsubmit="return confirm('Hapus catatan arus kas ini?')" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-stone-400 hover:text-red-600 transition" title="Hapus Mutasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-xs font-mono text-stone-400">
                                    Belum ada catatan mutasi kas pada tanggal ini. Klik "+ Pengeluaran" atau "+ Pemasukan" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mutations->hasPages())
                <div class="pt-4 border-t-2 border-stone-100">
                    {{ $mutations->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: Catat Pengeluaran Baru -->
        <div x-show="expenseModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bauhaus-card max-w-lg w-full p-5 bg-white shadow-modal animate-in fade-in"
                 @click.outside="expenseModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b-2 border-stone-900 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#DC2626] border border-black"></span>
                        <h3 class="text-sm font-display font-black text-[#121212] uppercase tracking-wide">
                            Catat Pengeluaran Kas (Outflow)
                        </h3>
                    </div>
                    <button type="button" @click="expenseModalOpen = false" class="text-stone-900 hover:text-red-600 font-black p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.cashflow.expense') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Kategori Pengeluaran <span class="text-red-500">*</span></label>
                            <select name="category" required class="bauhaus-input w-full font-mono">
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
                            <label class="block font-black text-stone-700 uppercase mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" 
                                   name="amount" 
                                   min="1" 
                                   step="1" 
                                   placeholder="Contoh: 75000" 
                                   required 
                                   class="bauhaus-input w-full font-mono text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Sumber Kas <span class="text-red-500">*</span></label>
                            <select name="source_account" required class="bauhaus-input w-full font-mono">
                                <option value="CASH_DRAWER">Laci Kasir (Cash Drawer)</option>
                                <option value="PETTY_CASH">Petty Cash Kasir</option>
                                <option value="BANK_ACCOUNT">Rekening Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   name="transaction_date" 
                                   value="{{ date('Y-m-d') }}" 
                                   required 
                                   class="bauhaus-input w-full font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-black text-stone-700 uppercase mb-1">Keterangan Pengeluaran <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="description" 
                               placeholder="Contoh: Beli bumbu pelengkap dan minyak goreng di pasar" 
                               required 
                               class="bauhaus-input w-full">
                    </div>

                    <div>
                        <label class="block font-black text-stone-700 uppercase mb-1">Upload Foto Nota / Struk (Opsional)</label>
                        <input type="file" 
                               name="receipt_image" 
                               accept="image/*" 
                               class="w-full text-xs text-stone-700 file:mr-2 file:py-1 file:px-2.5 file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FACC15] file:text-[#121212] cursor-pointer">
                    </div>

                    <div class="p-2.5 bg-amber-50 border-2 border-stone-800 flex items-center gap-2">
                        <input type="checkbox" 
                               name="is_h_minus_one_deduction" 
                               id="is_h_minus_one_exp" 
                               value="1" 
                               checked 
                               class="border-2 border-black text-[#1D4ED8] focus:ring-0">
                        <label for="is_h_minus_one_exp" class="font-bold text-stone-800 cursor-pointer">
                            Potong dari Omzet H-1 (Perputaran Uang Harian)
                        </label>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t-2 border-stone-200">
                        <button type="button" @click="expenseModalOpen = false" class="bauhaus-btn-white">Batal</button>
                        <button type="submit" class="bauhaus-btn-red">Simpan Pengeluaran</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Catat Pemasukan Baru -->
        <div x-show="incomeModalOpen" 
             class="fixed inset-0 bg-stone-900/60 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bauhaus-card max-w-lg w-full p-5 bg-white shadow-modal animate-in fade-in"
                 @click.outside="incomeModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b-2 border-stone-900 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#1D4ED8] border border-black"></span>
                        <h3 class="text-sm font-display font-black text-[#121212] uppercase tracking-wide">
                            Catat Pemasukan Kas (Inflow)
                        </h3>
                    </div>
                    <button type="button" @click="incomeModalOpen = false" class="text-stone-900 hover:text-red-600 font-black p-1">✕</button>
                </div>

                <form method="POST" action="{{ route('backoffice.cashflow.income') }}" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Kategori Pemasukan <span class="text-red-500">*</span></label>
                            <select name="category" required class="bauhaus-input w-full font-mono">
                                <option value="CAPITAL_IN">Setoran Modal / Kas Tambahan</option>
                                <option value="SALES_CASH">Setoran Tunai Manual</option>
                                <option value="OTHER_INCOME">Pendapatan Lain-lain</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" 
                                   name="amount" 
                                   min="1" 
                                   step="1" 
                                   placeholder="Contoh: 500000" 
                                   required 
                                   class="bauhaus-input w-full font-mono text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Masuk ke Rekening/Kas <span class="text-red-500">*</span></label>
                            <select name="source_account" required class="bauhaus-input w-full font-mono">
                                <option value="CASH_DRAWER">Laci Kasir (Cash Drawer)</option>
                                <option value="PETTY_CASH">Petty Cash Kasir</option>
                                <option value="BANK_ACCOUNT">Rekening Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-black text-stone-700 uppercase mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   name="transaction_date" 
                                   value="{{ date('Y-m-d') }}" 
                                   required 
                                   class="bauhaus-input w-full font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-black text-stone-700 uppercase mb-1">Keterangan Pemasukan <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="description" 
                               placeholder="Contoh: Setoran uang kembalian kasir dari owner" 
                               required 
                               class="bauhaus-input w-full">
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t-2 border-stone-200">
                        <button type="button" @click="incomeModalOpen = false" class="bauhaus-btn-white">Batal</button>
                        <button type="submit" class="bauhaus-btn-blue">Simpan Pemasukan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Preview Foto Struk Transaksi -->
        <div x-show="receiptPreviewModal" 
             class="fixed inset-0 bg-stone-900/80 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bauhaus-card max-w-xl w-full p-4 bg-white shadow-modal animate-in fade-in"
                 @click.outside="receiptPreviewModal = false">
                <div class="flex items-center justify-between pb-2 border-b-2 border-stone-900 mb-3">
                    <span class="text-xs font-black uppercase font-mono text-stone-800">BUKTI FOTO STRUK BELANJA</span>
                    <button type="button" @click="receiptPreviewModal = false" class="text-stone-900 hover:text-red-600 font-black p-1">✕ Tutup</button>
                </div>
                <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-stone-100 p-2 border-2 border-stone-800">
                    <img :src="previewImageUrl" alt="Foto Struk" class="max-h-[70vh] object-contain">
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
