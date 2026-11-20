<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Backoffice Resto</title>

    <!-- Google Fonts: Outfit (Display) & Plus Jakarta Sans (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#FFF8F0] text-[#2D1E1B] selection:bg-[#F97316] selection:text-white"
      x-data="{ sidebarOpen: false }"
      @keydown.window.escape="sidebarOpen = false">

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 md:hidden"></div>

        <!-- Sidebar Navigation (Deep Maroon #5B0A14 with SLL Brand Aesthetics) -->
        <aside class="fixed md:sticky md:top-0 inset-y-0 left-0 z-50 w-72 md:h-screen bg-[#5B0A14] text-white border-r border-[#4D0811] flex flex-col justify-between transition-transform duration-300 md:translate-x-0 shadow-brand-maroon shrink-0 select-none overflow-hidden"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <div class="flex-1 flex flex-col min-h-0 relative">
                <!-- Subtle Warm Ambient Glow in Maroon Corner -->
                <div class="absolute -top-16 -left-16 w-48 h-48 rounded-full bg-[#FF8A00]/15 blur-3xl pointer-events-none"></div>

                <!-- Brand Header -->
                <div class="h-20 px-5 border-b border-[#4D0811]/90 flex items-center justify-between bg-[#4D0811]/60 shrink-0 relative z-10">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <!-- Chunky SLL Gold & Orange Badge -->
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#FFC107] via-[#FF8A00] to-[#E11D48] text-white flex items-center justify-center font-display font-black text-xl shadow-lg shadow-black/30 ring-2 ring-[#FFC107]/50 group-hover:scale-105 transition transform">
                            <span class="tracking-tighter drop-shadow-md">SLL</span>
                        </div>
                        <div>
                            <span class="font-display font-black text-sm text-white tracking-tight block leading-tight">SOTO LAMONGAN</span>
                            <span class="block text-[10px] text-[#FFB800] font-black uppercase tracking-widest mt-0.5">LESTARI • RESTO POS</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden p-1.5 rounded-xl text-rose-200/60 hover:text-white hover:bg-white/10 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Outlet Info Card (Street-Food Banner Style) -->
                <div class="p-3.5 mx-4 mt-4 rounded-2xl bg-[#4D0811]/80 border border-white/10 shadow-inner flex items-center justify-between shrink-0 relative z-10">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-black uppercase tracking-wider text-[#FFC107]">Cabang Aktif</span>
                        </div>
                        <h4 class="text-xs font-black text-white truncate">SLL Cabang Utama</h4>
                        <span class="text-[10px] text-rose-200/70 font-medium block">📞 +62 852-1325-4219</span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-emerald-500/20 border border-emerald-400/30 text-[10px] font-bold text-emerald-300 shrink-0">
                        Online
                    </span>
                </div>

                <!-- Nav Menu Links -->
                <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto relative z-10">
                    <!-- Primary Cashier Action Button (Vibrant Orange CTA) -->
                    <div>
                        <a href="{{ route('pos.index') }}" 
                           class="flex items-center justify-between px-4 py-3 rounded-2xl font-display font-black text-xs bg-gradient-to-r from-[#F97316] via-[#FF8A00] to-[#EA580C] text-white shadow-brand-orange hover:brightness-110 active:scale-[0.98] transition group">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base group-hover:scale-110 transition transform">🛒</span>
                                <span>Buka Layar Kasir</span>
                            </div>
                            <span class="text-[10px] bg-black/25 text-[#FFC107] px-2 py-0.5 rounded-lg font-mono font-black tracking-wider">0ms</span>
                        </a>
                    </div>

                    <!-- Group 1: Operasional -->
                    <div>
                        <span class="px-3 text-[10px] font-black uppercase tracking-widest text-[#FFC107]/60">Operasional Resto</span>
                        <div class="mt-2 space-y-1">
                            <!-- Dashboard -->
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-rose-200/50 group-hover:text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span>Dashboard & Omzet</span>
                            </a>

                            <!-- Riwayat Pesanan -->
                            <a href="{{ route('backoffice.orders') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('backoffice.orders') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('backoffice.orders') ? 'text-white' : 'text-rose-200/50 group-hover:text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Riwayat Pesanan & Bon</span>
                            </a>
                        </div>
                    </div>

                    <!-- Group 2: Katalog & Dapur -->
                    <div>
                        <span class="px-3 text-[10px] font-black uppercase tracking-widest text-[#FFC107]/60">Katalog & Dapur</span>
                        <div class="mt-2 space-y-1">
                            <!-- Menu Produk & Resep -->
                            <a href="{{ route('backoffice.products') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('backoffice.products') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('backoffice.products') ? 'text-white' : 'text-rose-200/50 group-hover:text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>Menu Soto & Resep (BOM)</span>
                            </a>

                            <!-- Bahan Baku & Stok -->
                            <a href="{{ route('backoffice.inventory') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('backoffice.inventory') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('backoffice.inventory') ? 'text-white' : 'text-rose-200/50 group-hover:text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span>Bahan Baku & Gudang</span>
                            </a>
                        </div>
                    </div>

                    <!-- Group 3: AI Intelligence -->
                    <div>
                        <span class="px-3 text-[10px] font-black uppercase tracking-widest text-[#FFC107]/60">Kecerdasan Buatan</span>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('backoffice.ai-consultant') }}" 
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('backoffice.ai-consultant') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('backoffice.ai-consultant') ? 'text-white' : 'text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>AI Resto Consultant</span>
                                </div>
                                <span class="text-[9px] bg-[#FFC107] text-[#5B0A14] font-black px-1.5 py-0.5 rounded-md shadow-xs">AI</span>
                            </a>
                        </div>
                    </div>

                    <!-- Group 4: Administrasi -->
                    <div>
                        <span class="px-3 text-[10px] font-black uppercase tracking-widest text-[#FFC107]/60">Administrasi</span>
                        <div class="mt-2 space-y-1">
                            <!-- Staf & PIN -->
                            <a href="{{ route('backoffice.users.index') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('backoffice.users.*') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('backoffice.users.*') ? 'text-white' : 'text-rose-200/50 group-hover:text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Manajemen Staf & PIN</span>
                            </a>

                            <!-- Profil -->
                            <a href="{{ route('profile.edit') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition group {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-[#F97316] to-[#EA580C] text-white shadow-brand-orange' : 'text-rose-100/75 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('profile.*') ? 'text-white' : 'text-rose-200/50 group-hover:text-[#FFC107]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Pengaturan Akun</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-[#4D0811] bg-[#4D0811]/90 shrink-0">
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 min-w-0 hover:opacity-90 transition group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#FFC107] to-[#F97316] text-[#5B0A14] font-display font-black flex items-center justify-center text-sm shrink-0 shadow-md">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-white truncate group-hover:text-[#FFC107] transition">{{ auth()->user()->name ?? 'Admin SLL' }}</h4>
                            <span class="inline-block text-[9px] font-black px-1.5 py-0.2 rounded bg-[#5B0A14] text-[#FFC107] border border-[#FFC107]/40 uppercase tracking-wider">
                                {{ auth()->user()->role ?? 'SUPERADMIN' }}
                            </span>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar / Log Out" class="p-2 text-rose-200/60 hover:text-white hover:bg-rose-500/20 rounded-xl transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-x-hidden bg-[#FFF8F0]">
            <!-- Top App Bar (Crisp Warm Surface) -->
            <header class="h-18 bg-white/95 backdrop-blur-md border-b border-[#EFE7DE] px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-brand-sm sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="md:hidden p-2 text-[#5B0A14] hover:bg-[#FDF2F4] rounded-xl transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-base sm:text-lg font-display font-black text-[#2D1E1B] tracking-tight">
                            @yield('page_title', 'Dashboard Backoffice')
                        </h2>
                        <span class="text-[11px] text-[#7A0F1C] font-bold hidden sm:inline-block">Soto Lamongan Lestari • SLL Cabang Utama</span>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3.5">
                    <!-- Date Pill -->
                    <span class="hidden lg:inline-flex items-center text-xs font-bold text-[#786B66] bg-[#FFF8F0] border border-[#EFE7DE] px-3.5 py-1.5 rounded-xl shadow-2xs">
                        📅 {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                    </span>

                    <!-- Quick Terminal Kasir Jump Button -->
                    <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#F97316] via-[#FF8A00] to-[#EA580C] hover:brightness-110 text-white font-display font-black text-xs tracking-wide shadow-brand-orange transition flex items-center gap-2 active:scale-95">
                        <span class="text-sm">⚡</span>
                        <span class="hidden sm:inline">Layar Kasir (POS)</span>
                        <span class="sm:hidden">POS</span>
                    </a>

                    <!-- User Avatar link to Profile -->
                    <a href="{{ route('profile.edit') }}" title="Pengaturan Profil" class="w-9 h-9 rounded-xl bg-[#5B0A14] text-[#FFC107] hover:bg-[#7A0F1C] border border-[#FFC107]/30 flex items-center justify-center text-xs font-display font-black shadow-brand-sm transition">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-8 overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>
