<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS Pintar') }} - Autentikasi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-[#e0f0fe] via-[#eaf4fe] to-[#f4f9ff] text-slate-800 min-h-screen flex flex-col justify-center items-center p-4 selection:bg-blue-600 selection:text-white">
    <!-- Brand Logo Header -->
    <div class="mb-6 text-center">
        <a href="/" class="inline-flex flex-col items-center gap-2 group">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-blue-500/25 group-hover:scale-105 transition">
                P
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">POS Pintar</h1>
                <p class="text-xs text-slate-500 font-medium">Smart Cloud POS & AI Business Consultant</p>
            </div>
        </a>
    </div>

    <!-- Auth Card Surface -->
    <div class="w-full sm:max-w-md bg-white/90 backdrop-blur-xl border border-blue-100/90 rounded-3xl p-6 sm:p-8 shadow-[0_20px_60px_rgba(37,99,235,0.08)]">
        {{ $slot }}
    </div>

    <!-- Simple Back link -->
    <div class="mt-6 text-center">
        <a href="/" class="text-xs font-semibold text-slate-500 hover:text-blue-600 transition flex items-center gap-1 justify-center">
            <span>← Kembali ke Halaman Utama</span>
        </a>
    </div>
</body>
</html>
