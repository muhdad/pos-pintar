<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Terminal Kasir (Bauhaus)</title>

    <!-- Typography: Outfit, Plus Jakarta Sans, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#F6F4ED] text-[#121212] select-none overflow-hidden">
    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar (Bauhaus Modernist Grid) -->
        <header class="h-16 bg-white border-b-2 border-[#121212] px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-[0_2px_0px_#121212] z-30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#1D4ED8] border-2 border-[#121212] text-[#FACC15] flex items-center justify-center font-display font-black text-base shadow-[2px_2px_0px_#DC2626] shrink-0">
                    SLL
                </div>
                <div>
                    <h1 class="text-sm font-display font-black text-[#121212] leading-none tracking-widest uppercase">SOTO LAMONGAN LESTARI</h1>
                    <span class="text-[11px] text-stone-600 font-mono font-medium">TERMINAL KASIR 0ms • TELP: +62 852-1325-4219</span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);">
                <!-- Online / Offline Status Badge (Bauhaus Pill) -->
                <div class="flex items-center gap-1.5 px-3 py-1 border-2 border-[#121212] text-xs font-mono font-black uppercase shadow-[2px_2px_0px_#121212]"
                     :class="online ? 'bg-emerald-100 text-emerald-950' : 'bg-amber-100 text-amber-950'">
                    <span class="w-2 h-2" :class="online ? 'bg-emerald-600 animate-pulse' : 'bg-amber-600'"></span>
                    <span x-text="online ? 'CLOUD ONLINE' : 'OFFLINE MODE (LOCAL)'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="bauhaus-btn-white">
                    <svg class="w-3.5 h-3.5 text-[#1D4ED8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printer Thermal</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="bauhaus-btn-blue">
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
