<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-base font-bold text-slate-800 tracking-tight">Masuk ke Sistem</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gunakan akun terdaftar untuk mengakses kasir atau backoffice.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Pengguna</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autofocus autocomplete="username"
                   placeholder="nama@sotolamongan.com"
                   class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 shadow-xs transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-medium text-emerald-700 hover:underline" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 shadow-xs transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-0.5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-400 border-slate-300" name="remember">
                <span class="ms-2 text-xs text-slate-600">Ingat di perangkat ini</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs tracking-wider uppercase transition shadow-xs flex items-center justify-center gap-1.5 active:translate-y-px">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </form>

    <!-- Quick Role Demo Helper -->
    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-2">Bantuan Cepat Akun Demo:</span>
        <div class="flex items-center justify-center gap-1.5 flex-wrap">
            <button type="button" 
                    onclick="document.getElementById('email').value='admin@sotolamongan.com'; document.getElementById('password').value='password';"
                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-semibold transition">
                Owner / Admin
            </button>
            <button type="button" 
                    onclick="document.getElementById('email').value='kasir@sotolamongan.com'; document.getElementById('password').value='password';"
                    class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[10px] font-semibold transition border border-emerald-200">
                Kasir POS
            </button>
            <button type="button" 
                    onclick="document.getElementById('email').value='finance@sotolamongan.com'; document.getElementById('password').value='password';"
                    class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-800 text-[10px] font-semibold transition border border-sky-200">
                Finance
            </button>
        </div>
    </div>
</x-guest-layout>
