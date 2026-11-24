<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-base font-bold text-slate-800 tracking-tight">Lupa Kata Sandi</h2>
        <p class="text-xs text-slate-500 mt-0.5">Masukkan alamat email akun Anda untuk menerima tautan reset kata sandi.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email Pengguna" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('login') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                &larr; Kembali ke login
            </a>
            <x-primary-button>
                Kirim Tautan Reset
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
