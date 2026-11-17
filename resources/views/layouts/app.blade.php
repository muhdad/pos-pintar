<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Backoffice Resto</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#fdfaf6] text-slate-800 selection:bg-amber-500 selection:text-white"
      x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-cloak
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 md:hidden"></div>

        <!-- Sidebar Navigation -->
        <aside class="fixed md:static inset-y-0 left-0 z-50 w-72 bg-white border-r border-amber-100/90 flex flex-col justify-between transition-transform duration-300 md:translate-x-0 shadow-xs"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            <div>
                <!-- Brand Header -->
                <div class="h-18 px-5 border-b border-amber-100 flex items-center justify-between bg-gradient-to-b from-[#fffbf7] to-white">
                    <a href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-700 text-amber-950 flex items-center justify-center font-black text-xl shadow-md shadow-orange-500/25 border border-amber-300/40 group-hover:scale-105 transition">
                            <span class="tracking-tighter drop-shadow-xs font-black">SLL</span>
                        </div>
                        <div>
                            <span class="font-black text-sm text-slate-900 tracking-tight block leading-tight">SOTO LAMONGAN</span>
                            <span class="block text-[10px] text-amber-600 font-extrabold uppercase tracking-widest">LESTARI • RESTO POS</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <!-- Outlet Info Pill -->
                <div class="p-3.5 mx-4 mt-4 rounded-2xl bg-amber-50/70 border border-amber-200/60 flex items-center justify-between">
                    <div class="min-w-0">
                        <span class="block text-[10px] font-bold text-amber-800/80 uppercase tracking-wider">Restoran & Outlet</span>
                        <h4 class="text-xs font-black text-slate-900 truncate">SLL Cabang Utama</h4>
                        <span class="text-[10px] text-slate-500 block">📞 +62 852-1325-4219</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Aktif</span>
                    </span>
                </div>

                <!-- Nav Menu Links -->
                <nav class="px-4 py-4 space-y-6 overflow-y-auto">
                    <!-- Section: Operasional -->
                    <div>
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Operasional Resto</span>
                        <div class="mt-2 space-y-1">
                            <!-- Terminal Kasir Button Highlighted (Warm Fiery Amber/Orange) -->
                            <a href="{{ route('pos.index') }}" 
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white shadow-md shadow-amber-600/25 hover:from-amber-500 hover:to-orange-600 transition group">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-amber-200 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span>Buka Terminal Kasir</span>
                                </div>
                                <span class="text-[9px] bg-white/20 px-1.5 py-0.5 rounded-md font-bold">0ms</span>
                            </a>

                            <!-- Dashboard Link -->
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-amber-100/70 text-amber-950 font-bold border border-amber-300/80 shadow-2xs' : 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-950' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-amber-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span>Dashboard & Omzet</span>
                            </a>
                        </div>
                    </div>

                    <!-- Section: Katalog & Inventori -->
                    <div>
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Katalog Menu & Bahan</span>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('backoffice.products') }}" 
                               class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('backoffice.products') ? 'bg-amber-100/70 text-amber-950 font-bold border border-amber-300/80 shadow-2xs' : 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-950' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('backoffice.products') ? 'text-amber-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>Menu Soto & Resep (BOM)</span>
                            </a>

                            <a href="{{ route('backoffice.inventory') }}" 
                               class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('backoffice.inventory') ? 'bg-amber-100/70 text-amber-950 font-bold border border-amber-300/80 shadow-2xs' : 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-950' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('backoffice.inventory') ? 'text-amber-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span>Bahan Baku & Stok Gudang</span>
                            </a>

                            <a href="{{ route('backoffice.orders') }}" 
                               class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('backoffice.orders') ? 'bg-amber-100/70 text-amber-950 font-bold border border-amber-300/80 shadow-2xs' : 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-950' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('backoffice.orders') ? 'text-amber-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Riwayat Pesanan & Bon</span>
                            </a>
                        </div>
                    </div>

                    <!-- Section: AI Intelligence -->
                    <div>
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Kecerdasan Buatan</span>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('backoffice.ai-consultant') }}" 
                               class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('backoffice.ai-consultant') ? 'bg-amber-100/70 text-amber-950 font-bold border border-amber-300/80 shadow-2xs' : 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-950' }}">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>AI Resto Consultant</span>
                            </a>
                        </div>
                    </div>

                    <!-- Section: Administrasi & Akun -->
                    <div>
                        <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Administrasi</span>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('backoffice.users.index') }}" 
                               class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('backoffice.users.*') ? 'bg-amber-100/70 text-amber-950 font-bold border border-amber-300/80 shadow-2xs' : 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-950' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('backoffice.users.*') ? 'text-amber-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Manajemen Staf & PIN Kasir</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-amber-100 bg-[#fffdfa]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 text-amber-950 font-black flex items-center justify-center text-xs shrink-0 shadow-xs">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name ?? 'Admin SLL' }}</h4>
                            <span class="inline-block text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">
                                {{ auth()->user()->role ?? 'SUPERADMIN' }}
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top App Bar -->
            <header class="h-16 bg-white/95 backdrop-blur-md border-b border-amber-100 px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-2xs">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="md:hidden p-2 text-slate-600 hover:bg-amber-50 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">
                            @yield('page_title', 'Dashboard Backoffice')
                        </h2>
                        <span class="text-[11px] text-amber-800/80 font-semibold hidden sm:inline-block">Soto Lamongan Lestari • SLL Cabang Utama (Pesanan: +62 852-1325-4219)</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Terminal Kasir Jump Button -->
                    <a href="{{ route('pos.index') }}" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs tracking-wide shadow-sm shadow-orange-500/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="hidden sm:inline">Layar Kasir (POS)</span>
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
