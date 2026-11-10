<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">Daftar Akun Usaha Baru</h2>
        <p class="text-xs text-slate-500">Mulai kelola kasir pintar dan inventori multi-cabang Anda.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Pemilik / Brand</label>
            <input id="name" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required autofocus autocomplete="name"
                   placeholder="Contoh: Budi Santoso"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Bisnis</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autocomplete="username"
                   placeholder="owner@brandanda.com"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Password</label>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required autocomplete="new-password"
                   placeholder="Minimal 8 karakter"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" 
                   type="password" 
                   name="password_confirmation" 
                   required autocomplete="new-password"
                   placeholder="Ulangi password di atas"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
            <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs tracking-wide shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2">
                <span>Daftar & Buat Toko</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

        <div class="mt-5 text-center text-xs text-slate-500">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-700 underline">Masuk di sini</a>
        </div>
    </form>
</x-guest-layout>
