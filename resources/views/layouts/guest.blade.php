<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Masuk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#FAF6F0] text-[#292524] min-h-screen flex flex-col justify-center items-center p-4 selection:bg-[#EA580C] selection:text-white">
    <!-- Brand Header -->
    <div class="mb-6 text-center">
        <div class="inline-flex flex-col items-center gap-2">
            <div class="w-12 h-12 rounded-[10px] bg-[#5B0A14] text-white flex items-center justify-center font-display font-black text-lg border border-[#7A0F1C]">
                SLL
            </div>
            <div>
                <h1 class="text-xl font-display font-extrabold text-[#5B0A14] tracking-tight">SOTO LAMONGAN LESTARI</h1>
                <p class="text-xs text-stone-500 font-medium mt-0.5">Sistem Operasional Kasir & Backoffice Restoran</p>
            </div>
        </div>
    </div>

    <!-- Auth Card -->
    <div class="w-full sm:max-w-md bg-white border border-[#E7E0D8] rounded-[10px] p-6 sm:p-7 shadow-subtle">
        {{ $slot }}
    </div>

    <!-- Footer -->
    <div class="mt-6 text-center text-xs text-stone-400 font-medium">
        &copy; {{ date('Y') }} Soto Lamongan Lestari. All rights reserved.
    </div>
</body>
</html>
