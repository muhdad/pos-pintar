<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Terminal Kasir</title>

    <!-- Typography: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#F8FAFC] text-slate-800 select-none overflow-hidden">
    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar (Modern Clean Slate Grid) -->
        <header class="h-14 bg-[#0F172A] border-b border-slate-800 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-xs z-30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                    🍜
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white leading-none tracking-wide">SOTO LAMONGAN LESTARI</h1>
                    <span class="text-[11px] text-slate-400 font-medium">Smart Cloud POS • 0ms Latency</span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2.5" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);">
                <!-- Online / Offline Status Badge (Pastel Pill) -->
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold"
                     :class="online ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' : 'bg-amber-500/15 border border-amber-500/30 text-amber-300'">
                    <span class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                    <span x-text="online ? 'Cloud Online' : 'Offline Mode'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/15 text-slate-200 border border-white/10 text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printer Thermal</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1">
                    <span>Backoffice</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Main POS Screen Container -->
        <main class="flex-1 flex overflow-hidden">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
