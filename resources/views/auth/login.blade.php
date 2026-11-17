<x-guest-layout>
    <!-- Title -->
    <div class="mb-5 text-center">
        <h2 class="text-xl font-black text-stone-900 tracking-tight">Masuk ke Portal Resto</h2>
        <p class="text-xs text-stone-500 mt-0.5">Akses Backoffice & Terminal Kasir Soto Lamongan Lestari</p>
    </div>

    <!-- Quick Demo Credential Pills (Clean Soft Amber Card) -->
    <div class="mb-5 p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 text-xs space-y-1.5">
        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Akun Demo Siap Pakai:</div>
        <div class="flex items-center justify-between text-[11px] text-stone-700">
            <span>Admin: <code class="font-mono font-bold text-amber-800 bg-white px-1.5 py-0.5 rounded border border-amber-200">admin@pospintar.com</code></span>
            <span class="text-stone-500 font-mono text-[10px]">PIN: 1234</span>
        </div>
        <div class="flex items-center justify-between text-[11px] text-stone-700">
            <span>Kasir: <code class="font-mono font-bold text-amber-800 bg-white px-1.5 py-0.5 rounded border border-amber-200">kasir@sll-lestari.com</code></span>
            <span class="text-stone-500 font-mono text-[10px]">PIN: 1234</span>
        </div>
        <div class="text-[10px] text-stone-500 pt-0.5">Password: <code class="font-mono font-bold text-stone-800">admin123</code> / <code class="font-mono font-bold text-stone-800">password</code></div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-stone-700 mb-1">Alamat Email</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email', 'admin@pospintar.com') }}" 
                   required autofocus autocomplete="username"
                   placeholder="admin@pospintar.com"
                   class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs text-stone-900 placeholder-stone-400 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/25 focus:border-amber-500 transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-bold text-stone-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-semibold text-amber-700 hover:text-amber-800" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" 
                   type="password" 
                   name="password" 
                   value="admin123"
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs text-stone-900 placeholder-stone-400 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/25 focus:border-amber-500 transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-amber-600 shadow-xs focus:ring-amber-500" name="remember" checked>
                <span class="ms-2 text-xs text-stone-600 font-medium">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="mt-5">
            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white font-black text-xs tracking-wider uppercase shadow-md shadow-orange-500/25 transition flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="mt-5 text-center text-xs text-stone-500">
                Belum punya akun resto? 
                <a href="{{ route('register') }}" class="font-bold text-amber-700 hover:text-amber-800 underline">Daftar Akun Baru</a>
            </div>
        @endif
    </form>
</x-guest-layout>
