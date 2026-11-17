<x-app-layout>
    @section('page_title', 'Pengaturan Profil & Akun')

    <div class="space-y-6 max-w-4xl">
        <!-- Header Subtitle -->
        <div class="pb-1 border-b border-stone-200/60">
            <h1 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight">Pengaturan Profil & Sandi</h1>
            <p class="text-xs text-stone-500 mt-0.5">Kelola informasi akun pengguna dan pembaruan kata sandi resto.</p>
        </div>

        <!-- Update Profile Info Card -->
        <div class="p-6 bg-white border border-stone-200/80 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password Card -->
        <div class="p-6 bg-white border border-stone-200/80 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account Card -->
        <div class="p-6 bg-white border border-stone-200/80 rounded-2xl shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>