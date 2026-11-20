<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-base font-display font-bold text-stone-900 tracking-tight">Masuk ke Sistem</h2>
        <p class="text-xs text-stone-500 mt-0.5">Gunakan akun terdaftar untuk mengakses kasir atau backoffice.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-stone-700 mb-1">Email</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autofocus autocomplete="username"
                   placeholder="kasir@restoran.id"
                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:ring-1 focus:ring-[#EA580C] focus:border-[#EA580C] transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-semibold text-stone-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-medium text-[#C2410C] hover:text-[#9A3412]" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:ring-1 focus:ring-[#EA580C] focus:border-[#EA580C] transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded-[4px] border-[#E7E0D8] text-[#EA580C] focus:ring-[#EA580C]" name="remember">
                <span class="ms-2 text-xs text-stone-600">Ingat di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button (Min 44px tap target, 6px radius) -->
        <div class="pt-2">
            <button type="submit" class="w-full h-10 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wider uppercase transition shadow-subtle flex items-center justify-center gap-1.5 active:translate-y-px">
                <span>Masuk Sekarang</span>
            </button>
        </div>
    </form>
</x-guest-layout>
