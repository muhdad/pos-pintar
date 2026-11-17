<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Portal Masuk</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-[#fdfbf7] via-[#fff9ee] to-[#fbf5e9] text-stone-800 min-h-screen flex flex-col justify-center items-center p-4 selection:bg-amber-500 selection:text-white relative overflow-x-hidden">
    <!-- Soft Warm Ambient Glow (Non-Fatiguing) -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-96 h-96 rounded-full bg-orange-400/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 left-1/2 -translate-x-1/2 w-96 h-96 rounded-full bg-amber-300/15 blur-3xl pointer-events-none"></div>

    <!-- Brand Logo Header -->
    <div class="mb-6 text-center relative z-10">
        <a href="/" class="inline-flex flex-col items-center gap-3 group">
            <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 text-white flex items-center justify-center font-black text-3xl shadow-xl shadow-orange-500/20 border-2 border-amber-300/60 group-hover:scale-105 transition transform">
                <span class="tracking-tighter drop-shadow-xs font-black">SLL</span>
            </div>
            <div>
                <h1 class="text-2xl font-black text-stone-900 tracking-tight">SOTO LAMONGAN LESTARI</h1>
                <p class="text-xs text-amber-700 font-bold tracking-wide mt-0.5">Pusat Kuliner Lamongan • Pesanan: +62 852-1325-4219</p>
            </div>
        </a>
    </div>

    <!-- Auth Card Surface (Crisp Clean White Card) -->
    <div class="w-full sm:max-w-md bg-white/95 backdrop-blur-xl border border-stone-200/90 rounded-3xl p-6 sm:p-8 shadow-[0_20px_50px_rgba(245,158,11,0.06)] relative z-10">
        {{ $slot }}
    </div>

    <!-- Simple Back link -->
    <div class="mt-6 text-center relative z-10">
        <a href="/" class="text-xs font-semibold text-stone-500 hover:text-amber-700 transition flex items-center gap-1 justify-center">
            <span>← Kembali ke Halaman Utama</span>
        </a>
    </div>
</body>
</html>
