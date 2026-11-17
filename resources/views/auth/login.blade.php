<x-guest-layout>
    <!-- Title -->
    <div class="mb-5 text-center">
        <h2 class="text-lg font-black text-white tracking-tight">Masuk ke Portal Resto</h2>
        <p class="text-xs text-amber-200/70 mt-0.5">Akses Backoffice & Terminal Kasir Soto Lamongan Lestari</p>
    </div>

    <!-- Quick Demo Credential Pills -->
    <div class="mb-5 p-3 rounded-2xl bg-black/30 border border-amber-500/20 text-xs space-y-1.5">
        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Akun Demo Siap Pakai:</div>
        <div class="flex items-center justify-between text-[11px] text-amber-100/90">
            <span>Admin: <code class="font-mono font-bold text-amber-300">admin@pospintar.com</code></span>
            <span class="text-amber-200/60 font-mono">PIN: 1234</span>
        </div>
        <div class="flex items-center justify-between text-[11px] text-amber-100/90">
            <span>Kasir: <code class="font-mono font-bold text-amber-300">kasir@sll-lestari.com</code></span>
            <span class="text-amber-200/60 font-mono">PIN: 1234</span>
        </div>
        <div class="text-[10px] text-amber-200/50 pt-0.5">Password default: <code class="font-mono text-amber-300">password</code> / <code class="font-mono text-amber-300">admin123</code></div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-amber-200 mb-1">Alamat Email</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email', 'admin@pospintar.com') }}" 
                   required autofocus autocomplete="username"
                   placeholder="admin@pospintar.com"
                   class="w-full bg-black/40 border border-amber-500/30 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-amber-200/30 font-medium focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-400 transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-bold text-amber-200">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-semibold text-amber-400 hover:text-amber-300" href="{{ route('password.request') }}">
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
                   class="w-full bg-black/40 border border-amber-500/30 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-amber-200/30 font-medium focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-400 transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-amber-500/40 bg-black/40 text-amber-500 shadow-xs focus:ring-amber-500 focus:ring-offset-0" name="remember" checked>
                <span class="ms-2 text-xs text-amber-200/80 font-medium">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="mt-5">
            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-400 hover:to-orange-500 text-amber-950 font-black text-xs tracking-wider uppercase shadow-lg shadow-orange-600/30 transition flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="mt-5 text-center text-xs text-amber-200/60">
                Belum punya akun resto? 
                <a href="{{ route('register') }}" class="font-bold text-amber-400 hover:text-amber-300 underline">Daftar Akun Baru</a>
            </div>
        @endif
    </form>
</x-guest-layout>
