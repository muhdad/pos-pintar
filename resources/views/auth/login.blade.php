<x-guest-layout>
    <!-- Title -->
    <div class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">Masuk ke Akun Anda</h2>
        <p class="text-xs text-slate-500">Pilih mode login atau masukkan kredensial terdaftar.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email', 'owner@kopisenja.com') }}" 
                   required autofocus autocomplete="username"
                   placeholder="nama@toko.com"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-bold text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-semibold text-blue-600 hover:text-blue-700" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" 
                   type="password" 
                   name="password" 
                   value="password"
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-md border-slate-300 text-blue-600 shadow-xs focus:ring-blue-500" name="remember" checked>
                <span class="ms-2 text-xs text-slate-600 font-medium">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="mt-5">
            <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs tracking-wide shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="mt-5 text-center text-xs text-slate-500">
                Belum punya akun usaha? 
                <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700 underline">Daftar Akun Baru</a>
            </div>
        @endif
    </form>
</x-guest-layout>
