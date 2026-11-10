<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS Pintar') }} - Kasir</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-gradient-to-br from-[#e0f0fe] via-[#eaf4fe] to-[#f4f9ff] text-slate-800 select-none overflow-hidden">
    <div class="h-screen w-screen flex flex-col">
        <!-- Top Cashier Header Bar -->
        <header class="h-14 bg-white/80 backdrop-blur-md border-b border-blue-100/80 px-4 flex items-center justify-between shrink-0 shadow-sm z-30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/20">
                    P
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-none">POS Pintar</h1>
                    <span class="text-xs text-slate-500 font-medium">Terminal Kasir Fullscreen</span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-3" x-data="{ online: navigator.onLine }" x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false);">
                <!-- Online / Offline Status Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                     :class="online ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                    <span class="w-2 h-2 rounded-full" :class="online ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                    <span x-text="online ? 'Cloud Online' : 'Offline Mode (Local DB)'"></span>
                </div>

                <!-- Bluetooth Printer Button -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Hubungkan Printer</span>
                </button>

                <!-- Switch to Backoffice -->
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition flex items-center gap-1">
                    <span>Backoffice</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
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

