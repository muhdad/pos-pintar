<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Masuk</title>

    <!-- Typography: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#F8FAFC] text-slate-800 min-h-screen flex flex-col justify-center items-center p-4 selection:bg-emerald-500 selection:text-white">
    <!-- Brand Header -->
    <div class="mb-6 text-center">
        <div class="inline-flex flex-col items-center gap-2.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center font-bold text-2xl shadow-sm">
                🍜
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">SOTO LAMONGAN LESTARI</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Smart Cloud POS & Backoffice Restoran</p>
            </div>
        </div>
    </div>

    <!-- Auth Card -->
    <div class="w-full sm:max-w-md bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-sm">
        {{ $slot }}
    </div>

    <!-- Footer -->
    <div class="mt-6 text-center text-xs text-slate-400 font-medium">
        &copy; {{ date('Y') }} Soto Lamongan Lestari. All rights reserved.
    </div>
</body>
</html>
