<x-guest-layout>
    <!-- Title -->
    <div class="mb-6 text-center">
        <h2 class="text-xl font-display font-black text-stone-900 tracking-tight">Masuk ke Portal Resto</h2>
        <p class="text-xs text-stone-500 mt-1">Akses Backoffice & Terminal Kasir Soto Lamongan Lestari</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold text-stone-700 mb-1.5">Alamat Email</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autofocus autocomplete="username"
                   placeholder="nama@restoran.com"
                   class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 text-xs text-stone-900 placeholder-stone-400 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF8A00]/30 focus:border-[#FF8A00] transition shadow-2xs">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-stone-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-bold text-[#E65100] hover:text-[#5B0A14] transition" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 text-xs text-stone-900 placeholder-stone-400 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF8A00]/30 focus:border-[#FF8A00] transition shadow-2xs">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-[#FF8A00] shadow-2xs focus:ring-[#FF8A00]" name="remember">
                <span class="ms-2 text-xs text-stone-600 font-medium">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button (Min 48px Tap Target) -->
        <div class="mt-6">
            <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-[#FF8A00] via-[#F97316] to-[#E65100] hover:from-[#FFA000] hover:to-[#FF8A00] text-white font-display font-black text-xs tracking-wider uppercase shadow-brand-orange active:scale-[0.98] transition flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </form>
</x-guest-layout>
