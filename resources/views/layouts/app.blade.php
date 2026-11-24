<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SLL - Soto Lamongan Lestari') }} - Backoffice</title>

    <!-- Typography: Plus Jakarta Sans for UI, JetBrains Mono for tabular numbers -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#F8FAFC] text-slate-800 selection:bg-emerald-500 selection:text-white"
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
             class="fixed inset-0 bg-slate-900/60 z-40 md:hidden"></div>

        <!-- Modern Soft Slate Sidebar Navigation -->
        <aside class="fixed md:sticky md:top-0 inset-y-0 left-0 z-50 w-64 md:h-screen bg-[#0F172A] text-slate-300 border-r border-slate-800 flex flex-col justify-between transition-transform duration-200 md:translate-x-0 shrink-0 select-none overflow-y-auto"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <div class="flex-1 flex flex-col min-h-0">
                <!-- Brand Header -->
                <div class="h-16 px-4 border-b border-slate-800/80 flex items-center justify-between shrink-0 bg-[#0B1120]">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-bold text-xs tracking-wider shadow-sm">
                            SLL
                        </div>
                        <div class="leading-tight">
                            <span class="font-bold text-xs text-white tracking-wide block">SOTO LAMONGAN</span>
                            <span class="text-[10px] text-emerald-400 font-medium tracking-wide block">Smart Cloud POS</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden p-1 text-slate-400 hover:text-white rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Outlet Meta Strip -->
                <div class="px-4 py-2.5 border-b border-slate-800/80 bg-slate-900/60 text-xs flex items-center justify-between">
                    <div class="min-w-0">
                        <span class="text-[10px] font-semibold text-slate-400 block">CABANG RESTO</span>
                        <span class="font-semibold text-slate-200 truncate block text-xs">{{ $outlet->name ?? 'Cabang Utama' }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        ONLINE
                    </span>
                </div>

                <!-- Navigation Groups -->
                <nav class="flex-1 px-3 py-4 space-y-4 text-xs font-medium">
                    <!-- Primary POS CTA (Soft Emerald) -->
                    <div>
                        <a href="{{ route('pos.index') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl shadow-sm transition active:translate-y-px">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Buka Layar Kasir</span>
                            </div>
                            <span class="text-[10px] bg-emerald-700/60 px-1.5 py-0.5 rounded text-emerald-100 font-mono">F2</span>
                        </a>
                    </div>

                    <!-- Group 1: Operasional -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">OPERASIONAL</span>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard Ringkas</span>
                        </a>
                        <a href="{{ route('backoffice.orders') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.orders*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Riwayat Transaksi</span>
                        </a>
                    </div>

                    <!-- Group 2: Katalog & Stok -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">KATALOG & GUDANG</span>
                        <a href="{{ route('backoffice.products') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.products*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Menu & Resep (BOM)</span>
                        </a>
                        <a href="{{ route('backoffice.inventory') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.inventory*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Stok Gudang (Auto Restock)</span>
                        </a>
                    </div>

                    <!-- Group 3: Keuangan & Arus Kas (Superadmin, Owner, Finance) -->
                    @if(auth()->user()?->isFinance())
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">KEUANGAN & KAS</span>
                        <a href="{{ route('backoffice.cashflow.index') }}" 
                           class="flex items-center justify-between px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.cashflow*') ? 'bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Arus Kas & Buku Kas</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-medium">KAS</span>
                        </a>
                    </div>
                    @endif

                    <!-- Group 4: Restock Bahan -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">PEMBELIAN BAHAN</span>
                        <a href="{{ route('backoffice.restock') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.restock*') ? 'bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Restock Bahan (Konversi)</span>
                        </a>
                    </div>

                    <!-- Group 5: Pengaturan -->
                    <div class="space-y-1">
                        <span class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">PENGATURAN</span>
                        <a href="{{ route('backoffice.settings.index') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.settings*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Outlet & QRIS Toko</span>
                        </a>
                        <a href="{{ route('backoffice.users.index') }}" 
                           class="flex items-center gap-2.5 px-3 py-2 text-xs rounded-xl transition {{ request()->routeIs('backoffice.users*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <svg class="w-4 h-4 text-inherit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Kelola Staf & Akun</span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Footer: User Session & Logout -->
            <div class="p-3 border-t border-slate-800/80 bg-[#0B1120] shrink-0">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-xs text-white truncate block">{{ auth()->user()->name ?? 'Administrator' }}</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300 font-semibold uppercase">{{ auth()->user()->role ?? 'STAF' }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 truncate block font-mono">{{ auth()->user()->email ?? 'admin@resto.id' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition"
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
            <!-- Modern Soft Top Bar -->
            <header class="h-14 bg-white border-b border-slate-200/80 px-3.5 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <button @click="sidebarOpen = true" class="md:hidden p-1.5 -ml-1 text-slate-700 hover:bg-slate-100 rounded-lg shrink-0" aria-label="Buka Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <span class="font-bold text-xs sm:text-sm text-slate-800 truncate block max-w-[160px] sm:max-w-none">@yield('page_title', 'Backoffice Resto')</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 text-xs shrink-0">
                    <span class="text-slate-500 font-medium hidden sm:inline">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <a href="{{ route('pos.index') }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5 active:translate-y-px">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="hidden sm:inline">Kasir POS</span>
                        <span class="sm:hidden">Kasir</span>
                    </a>
                </div>
            </header>

            <!-- Page Body Content (pb-28 on mobile to prevent bottom nav collision) -->
            <main class="flex-1 p-3.5 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto pb-28 md:pb-8">
                @if (session('status'))
                    <div class="mb-4 p-3.5 bg-emerald-50/90 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ session('status') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold p-1">✕</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-3.5 bg-rose-50/90 border border-rose-200 text-rose-800 text-xs font-semibold rounded-xl flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 font-bold p-1">✕</button>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <!-- Mobile Quick Access Bottom Navigation Bar (< md) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-slate-200/90 px-2 py-1.5 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]"
                 style="padding-bottom: max(0.375rem, env(safe-area-inset-bottom));">
                <div class="grid grid-cols-5 gap-1 text-center items-center">
                    <!-- 1. Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-emerald-700 font-bold bg-emerald-50/80' : 'text-slate-500 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="text-[9px] leading-tight block">Home</span>
                    </a>

                    <!-- 2. Restock Bahan -->
                    <a href="{{ route('backoffice.restock') }}" 
                       class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ request()->routeIs('backoffice.restock*') ? 'text-emerald-700 font-bold bg-emerald-50/80' : 'text-slate-500 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-[9px] leading-tight block">Restock</span>
                    </a>

                    <!-- 3. Highlighted POS Kasir Button -->
                    <a href="{{ route('pos.index') }}" 
                       class="flex flex-col items-center justify-center -mt-3.5 py-1 transition group">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center shadow-md border-2 border-white group-active:scale-95 transition">
                            <span class="text-lg">🍜</span>
                        </div>
                        <span class="text-[9px] font-bold text-emerald-800 leading-tight block mt-0.5">Kasir POS</span>
                    </a>

                    <!-- 4. Keuangan / Arus Kas -->
                    <a href="{{ route('backoffice.cashflow.index') }}" 
                       class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ request()->routeIs('backoffice.cashflow*') ? 'text-emerald-700 font-bold bg-emerald-50/80' : 'text-slate-500 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[9px] leading-tight block">Kas</span>
                    </a>

                    <!-- 5. Hamburger Menu -->
                    <button type="button" 
                            @click="sidebarOpen = true" 
                            class="flex flex-col items-center justify-center py-1 rounded-xl text-slate-500 hover:text-slate-900 transition">
                        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span class="text-[9px] leading-tight block">Menu</span>
                    </button>
                </div>
            </nav>
        </div>
    </div>
</body>
</html>