<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Terminal Kasir</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-gradient-to-br from-[#1c0404] via-[#2a0707] to-[#150202] text-amber-50 select-none overflow-hidden">
    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar -->
        <header class="h-16 bg-[#250505]/95 backdrop-blur-md border-b border-amber-900/60 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-lg z-30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-amber-600 text-[#230404] flex items-center justify-center font-black text-base shadow-md shadow-orange-600/30 ring-2 ring-amber-400/80 shrink-0">
                    SLL
                </div>
                <div>
                    <h1 class="text-sm font-black text-white leading-none tracking-wide">SOTO LAMONGAN LESTARI</h1>
                    <span class="text-xs text-amber-200/60 font-medium">Terminal Kasir 0ms • Pesanan: +62 852-1325-4219</span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);">
                <!-- Online / Offline Status Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                     :class="online ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/30' : 'bg-amber-950/80 text-amber-300 border border-amber-500/30'">
                    <span class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                    <span x-text="online ? 'Cloud Online' : 'Offline Mode (Local DB)'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-1.5 text-xs font-semibold text-amber-200 bg-black/40 border border-amber-500/30 rounded-xl hover:bg-black/60 flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printer Thermal</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs font-bold text-amber-950 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl hover:from-amber-400 hover:to-orange-400 transition flex items-center gap-1 shadow-sm">
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

