<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Portal Masuk</title>

    <!-- Brand Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#FFF8F0] text-stone-800 min-h-screen flex flex-col justify-center items-center p-4 selection:bg-[#FF8A00] selection:text-white relative overflow-x-hidden">
    <!-- Subtle Brand Glow Accents -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-96 h-96 rounded-full bg-[#FF8A00]/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 left-1/2 -translate-x-1/2 w-96 h-96 rounded-full bg-[#5B0A14]/10 blur-3xl pointer-events-none"></div>

    <!-- Brand Logo Header -->
    <div class="mb-6 text-center relative z-10">
        <div class="inline-flex flex-col items-center gap-2.5">
            <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-[#FFB800] via-[#FF8A00] to-[#E65100] text-white flex items-center justify-center font-display font-black text-2xl shadow-brand-orange ring-4 ring-[#FFC107]/40">
                SLL
            </div>
            <div>
                <h1 class="text-2xl font-display font-black text-[#5B0A14] tracking-tight">SOTO LAMONGAN LESTARI</h1>
                <p class="text-xs text-[#E65100] font-bold tracking-wide mt-0.5">Rasa Mantap, Harga Bersahabat • 📞 +62 852-1325-4219</p>
            </div>
        </div>
    </div>

    <!-- Auth Card Surface -->
    <div class="w-full sm:max-w-md bg-white border border-stone-200/90 rounded-3xl p-6 sm:p-8 shadow-brand-lg relative z-10">
        {{ $slot }}
    </div>

    <!-- Production Footer -->
    <div class="mt-6 text-center relative z-10">
        <p class="text-[11px] font-medium text-stone-500">
            &copy; {{ date('Y') }} Soto Lamongan Lestari (SLL) • Smart Cloud POS
        </p>
    </div>
</body>
</html>
