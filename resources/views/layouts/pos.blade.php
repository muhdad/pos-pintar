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
<body class="h-full antialiased font-sans bg-[#fbf9f5] text-stone-800 select-none overflow-hidden">
    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar (Clean White) -->
        <header class="h-16 bg-white/95 backdrop-blur-md border-b border-stone-200/90 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-xs z-30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-amber-600 text-white flex items-center justify-center font-black text-base shadow-md shadow-orange-500/25 ring-2 ring-amber-300 shrink-0">
                    SLL
                </div>
                <div>
                    <h1 class="text-sm font-black text-stone-900 leading-none tracking-wide">SOTO LAMONGAN LESTARI</h1>
                    <span class="text-xs text-stone-500 font-medium">Terminal Kasir 0ms • Pesanan: +62 852-1325-4219</span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);">
                <!-- Online / Offline Status Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                     :class="online ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                    <span class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                    <span x-text="online ? 'Cloud Online' : 'Mode Offline (Local DB)'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-1.5 text-xs font-semibold text-stone-700 bg-white border border-stone-200 rounded-xl hover:bg-stone-50 flex items-center gap-1.5 transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printer Thermal</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl transition flex items-center gap-1 shadow-xs shadow-orange-500/20">
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
