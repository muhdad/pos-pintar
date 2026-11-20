<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Backoffice</title>

    <!-- Typography: Outfit for headers, Plus Jakarta Sans for body, JetBrains Mono for tabular numbers -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#FAF6F0] text-[#292524] selection:bg-[#EA580C] selection:text-white"
      x-data="{ sidebarOpen: false }"
      @keydown.window.escape="sidebarOpen = false">

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-stone-900/50 z-40 md:hidden"></div>

        <!-- Sidebar Navigation (Deep Maroon #5B0A14 - Fixed 240px) -->
        <aside class="fixed md:sticky md:top-0 inset-y-0 left-0 z-50 w-60 md:h-screen bg-[#5B0A14] text-white border-r border-[#4A0710] flex flex-col justify-between transition-transform duration-200 md:translate-x-0 shrink-0 select-none overflow-y-auto"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <div class="flex-1 flex flex-col min-h-0">
                <!-- Brand Header -->
                <div class="h-16 px-4 border-b border-[#4A0710] flex items-center justify-between shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[6px] bg-[#EA580C] text-white flex items-center justify-center font-display font-black text-xs tracking-wider">
                            SLL
                        </div>
                        <div class="leading-tight">
                            <span class="font-display font-black text-xs text-white tracking-wide block">SOTO LAMONGAN</span>
                            <span class="text-[10px] text-[#FFB800] font-semibold tracking-wider block">LESTARI POS</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden p-1 rounded-[6px] text-stone-300 hover:text-white hover:bg-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Outlet Meta Strip -->
                <div class="px-4 py-3 border-b border-[#4A0710] bg-[#4A0710]/50 text-xs flex items-center justify-between">
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Outlet Aktif</span>
                        <span class="font-semibold text-stone-200 truncate block text-xs">{{ $outlet->name ?? 'Cabang Utama' }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-[4px] bg-emerald-950 border border-emerald-800 text-[10px] font-bold text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Online
                    </span>
                </div>

                <!-- Navigation Groups -->
                <nav class="flex-1 px-2.5 py-3 space-y-4 text-xs font-medium">
                    <!-- Primary POS CTA -->
                    <div>
                        <a href="{{ route('pos.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wide transition shadow-subtle active:translate-y-px">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Buka Layar Kasir</span>
                            </div>
                            <span class="text-[10px] bg-black/20 px-1 py-0.5 rounded-[4px] font-mono">F2</span>
                        </a>
                    </div>

                    <!-- Group 1: Operasional -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-stone-400">Operasional</span>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition {{ request()->routeIs('dashboard') ? 'bg-[#7A0F1C] text-white font-bold' : 'text-stone-300 hover:bg-[#6E0E1B] hover:text-white' }}">
                            <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard Ringkas</span>
                        </a>
                        <a href="{{ route('backoffice.orders') }}" 
                           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition {{ request()->routeIs('backoffice.orders*') ? 'bg-[#7A0F1C] text-white font-bold' : 'text-stone-300 hover:bg-[#6E0E1B] hover:text-white' }}">
                            <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Riwayat Transaksi</span>
                        </a>
                    </div>

                    <!-- Group 2: Menu & Bahan Baku -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-stone-400">Katalog & Stok</span>
                        <a href="{{ route('backoffice.products') }}" 
                           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition {{ request()->routeIs('backoffice.products*') ? 'bg-[#7A0F1C] text-white font-bold' : 'text-stone-300 hover:bg-[#6E0E1B] hover:text-white' }}">
                            <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Menu & Resep (BOM)</span>
                        </a>
                        <a href="{{ route('backoffice.inventory') }}" 
                           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition {{ request()->routeIs('backoffice.inventory*') ? 'bg-[#7A0F1C] text-white font-bold' : 'text-stone-300 hover:bg-[#6E0E1B] hover:text-white' }}">
                            <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Stok Bahan Baku</span>
                        </a>
                    </div>

                    <!-- Group 3: Analitik -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-stone-400">Analitik Resto</span>
                        <a href="{{ route('backoffice.ai-consultant') }}" 
                           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition {{ request()->routeIs('backoffice.ai-consultant*') ? 'bg-[#7A0F1C] text-white font-bold' : 'text-stone-300 hover:bg-[#6E0E1B] hover:text-white' }}">
                            <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Konsultan AI</span>
                        </a>
                    </div>

                    <!-- Group 4: Pengaturan -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-stone-400">Pengaturan</span>
                        <a href="{{ route('backoffice.users.index') }}" 
                           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[6px] text-xs transition {{ request()->routeIs('backoffice.users*') ? 'bg-[#7A0F1C] text-white font-bold' : 'text-stone-300 hover:bg-[#6E0E1B] hover:text-white' }}">
                            <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Kelola Staf & Akun</span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Footer: User Session & Logout -->
            <div class="p-3 border-t border-[#4A0710] bg-[#4A0710]/40 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <span class="font-bold text-xs text-white truncate block">{{ auth()->user()->name ?? 'Administrator' }}</span>
                        <span class="text-[10px] text-stone-400 truncate block">{{ auth()->user()->email ?? 'admin@resto.id' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="p-1.5 rounded-[6px] text-stone-400 hover:text-white hover:bg-white/10 transition"
                                title="Keluar dari sistem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
            <!-- Backoffice Top Header Bar -->
            <header class="h-14 bg-white border-b border-[#E7E0D8] px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="md:hidden p-1.5 -ml-1 text-stone-600 hover:text-stone-900 rounded-[6px]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <span class="font-display font-bold text-sm text-[#5B0A14] tracking-tight">@yield('page_title', 'Backoffice Resto')</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs">
                    <span class="text-stone-500 hidden sm:inline">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    <span class="text-stone-300 hidden sm:inline">|</span>
                    <a href="{{ route('pos.index') }}" class="px-3 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wide transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Kasir POS</span>
                    </a>
                </div>
            </header>

            <!-- Page Body Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
