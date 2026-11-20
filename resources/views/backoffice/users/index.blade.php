<x-app-layout>
    @section('page_title', 'Manajemen Pengguna')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-4 p-3 rounded-[6px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 rounded-[6px] bg-red-50 border border-red-200 text-red-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-[10px] font-bold">✕</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900 font-bold p-1">✕</button>
        </div>
    @endif

    <div x-data="{
        userModalOpen: false,
        isEditing: false,
        modalTitle: 'Tambah Pengguna Baru',
        formAction: '{{ route('backoffice.users.store') }}',
        formMethod: 'POST',
        userId: null,
        name: '',
        email: '',
        phone: '',
        role: 'CASHIER',
        password: '',
        pin: '',
        isActive: true,
        selectedOutlets: [],

        openCreateModal() {
            this.isEditing = false;
            this.modalTitle = 'Tambah Pengguna Baru';
            this.formAction = '{{ route('backoffice.users.store') }}';
            this.userId = null;
            this.name = '';
            this.email = '';
            this.phone = '';
            this.role = 'CASHIER';
            this.password = '';
            this.pin = '';
            this.isActive = true;
            this.selectedOutlets = ['{{ $outlets->first()->id ?? '' }}'];
            this.userModalOpen = true;
        },

        openEditModal(user) {
            this.isEditing = true;
            this.modalTitle = 'Edit Pengguna: ' + user.name;
            this.formAction = '/backoffice/users/' + user.id;
            this.userId = user.id;
            this.name = user.name;
            this.email = user.email;
            this.phone = user.phone || '';
            this.role = user.role;
            this.password = '';
            this.pin = '';
            this.isActive = Boolean(user.is_active);
            this.selectedOutlets = (user.outlets || []).map(o => String(o.id));
            this.userModalOpen = true;
        }
    }" class="space-y-5">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#E7E0D8]">
            <div>
                <h1 class="text-lg sm:text-xl font-display font-bold text-[#5B0A14] tracking-tight">Manajemen Staf & Hak Akses</h1>
                <p class="text-xs text-stone-500 mt-0.5">Kelola akun kasir, manajer, PIN akses kasir, dan penugasan outlet.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="openCreateModal()"
                        class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wide transition shadow-subtle flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Pengguna</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-[10px] p-3 border border-[#E7E0D8] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <form method="GET" action="{{ route('backoffice.users.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-60">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama, email, no. HP..." 
                           class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-3 py-1.5 pl-8 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select name="role" 
                        onchange="this.form.submit()" 
                        class="bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-700 font-medium focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    <option value="">Semua Peran</option>
                    <option value="SUPERADMIN" {{ request('role') === 'SUPERADMIN' ? 'selected' : '' }}>Superadmin</option>
                    <option value="OWNER" {{ request('role') === 'OWNER' ? 'selected' : '' }}>Owner</option>
                    <option value="MANAGER" {{ request('role') === 'MANAGER' ? 'selected' : '' }}>Manager</option>
                    <option value="CASHIER" {{ request('role') === 'CASHIER' ? 'selected' : '' }}>Kasir (Cashier)</option>
                </select>

                <button type="submit" class="px-3 py-1.5 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition">
                    Filter
                </button>

                @if(request('search') || request('role'))
                    <a href="{{ route('backoffice.users.index') }}" class="px-2 py-1.5 text-xs text-red-600 hover:underline">
                        Reset
                    </a>
                @endif
            </form>

            <span class="text-xs text-stone-500 font-mono">
                Total: <strong>{{ $users->total() }}</strong> staf
            </span>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-[10px] border border-[#E7E0D8] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-stone-500 font-bold uppercase text-[10px] tracking-wider border-b border-[#E7E0D8] bg-stone-50 sticky top-0 z-10">
                            <th class="py-2.5 px-4">Pengguna</th>
                            <th class="py-2.5 px-3">Peran (Role)</th>
                            <th class="py-2.5 px-3">Outlet Penugasan</th>
                            <th class="py-2.5 px-3 text-center">PIN Kasir</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-stone-50/70 transition">
                                <!-- User Info -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-[6px] bg-stone-100 border border-stone-200 flex items-center justify-center font-bold text-xs text-stone-700 shrink-0 font-mono">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-stone-900">{{ $user->name }}</span>
                                                @if($user->id === auth()->id())
                                                    <span class="text-[9px] font-bold px-1 py-0.2 rounded bg-stone-100 text-stone-600">Anda</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-stone-500 block">{{ $user->email }}</span>
                                            @if($user->phone)
                                                <span class="text-[10px] text-stone-400 font-mono">{{ $user->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge -->
                                <td class="py-3 px-3">
                                    <span class="inline-block px-2 py-0.5 rounded-[4px] text-[10px] font-bold border {{ $user->role === 'SUPERADMIN' ? 'bg-stone-900 text-white border-stone-900' : ($user->role === 'OWNER' ? 'bg-[#5B0A14] text-white border-[#5B0A14]' : ($user->role === 'MANAGER' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-stone-100 text-stone-700 border-stone-200')) }}">
                                        {{ $user->role }}
                                    </span>
                                </td>

                                <!-- Outlets -->
                                <td class="py-3 px-3 text-stone-600">
                                    @if($user->outlets->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($user->outlets as $outl)
                                                <span class="px-1.5 py-0.5 rounded-[4px] bg-stone-100 text-stone-700 text-[10px] font-medium">
                                                    {{ $outl->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-stone-400 text-[11px]">Semua Cabang</span>
                                    @endif
                                </td>

                                <!-- PIN Status -->
                                <td class="py-3 px-3 text-center">
                                    @if($user->pin_hash)
                                        <span class="inline-block px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono">
                                            4-Digit Aktif
                                        </span>
                                    @else
                                        <span class="text-[10px] text-stone-400">Belum diatur</span>
                                    @endif
                                </td>

                                <!-- Status Aktif -->
                                <td class="py-3 px-3 text-center">
                                    <form method="POST" action="{{ route('backoffice.users.toggle', $user->id) }}">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk ubah status" 
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                                class="px-2 py-0.5 rounded-[4px] text-[10px] font-bold transition {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-stone-100 text-stone-500 border border-stone-200 hover:bg-stone-200' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" 
                                                @click="openEditModal({{ json_encode($user) }})"
                                                class="px-2 py-1 rounded-[4px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 text-xs font-semibold transition">
                                            Edit
                                        </button>

                                        @if($user->id !== auth()->id())
                                            <form method="POST" 
                                                  action="{{ route('backoffice.users.destroy', $user->id) }}" 
                                                  onsubmit="return confirm('Hapus akun staf {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1 rounded-[4px] text-stone-400 hover:text-red-600 hover:bg-red-50 transition" 
                                                        title="Hapus Pengguna">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-stone-400">
                                    Belum ada staf terdaftar. Klik "+ Tambah Pengguna" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-3 border-t border-stone-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Create / Edit User Modal -->
        <div x-show="userModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-md w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="userModalOpen = false">
                
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide" x-text="modalTitle"></h3>
                        <p class="text-[11px] text-stone-500">Kelola akun dan kredensial login kasir atau manajer.</p>
                    </div>
                    <button type="button" @click="userModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <form :action="formAction" method="POST" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               x-model="name" 
                               required 
                               placeholder="Contoh: Rian Pratama" 
                               class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <!-- Email & No. HP Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" 
                                   name="email" 
                                   x-model="email" 
                                   required 
                                   placeholder="kasir@restoran.id" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" 
                                   name="phone" 
                                   x-model="phone" 
                                   placeholder="08123456789" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <!-- Role & PIN Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Peran Akses <span class="text-red-500">*</span></label>
                            <select name="role" 
                                    x-model="role" 
                                    required 
                                    class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                                <option value="CASHIER">Kasir (Terminal POS)</option>
                                <option value="MANAGER">Manager (Operasional & Stok)</option>
                                <option value="OWNER">Owner (Backoffice Penuh)</option>
                                <option value="SUPERADMIN">Superadmin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">
                                4-Digit PIN Kasir
                                <span class="text-[10px] text-stone-400 font-normal">(opsional)</span>
                            </label>
                            <input type="password" 
                                   name="pin" 
                                   x-model="pin" 
                                   maxlength="4" 
                                   placeholder="4 angka (misal: 1234)" 
                                   class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 font-mono tracking-widest focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">
                            Password Login Web
                            <span x-show="isEditing" class="text-[10px] text-stone-400 font-normal">(kosongkan jika tidak diubah)</span>
                            <span x-show="!isEditing" class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               x-model="password" 
                               :required="!isEditing" 
                               minlength="6" 
                               placeholder="Minimal 6 karakter" 
                               class="w-full bg-white border border-[#E7E0D8] rounded-[6px] px-2.5 py-1.5 text-xs text-stone-900 focus:outline-none focus:ring-1 focus:ring-[#EA580C]">
                    </div>

                    <!-- Outlet Penugasan -->
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Penugasan Outlet</label>
                        <div class="space-y-1 max-h-24 overflow-y-auto p-2 rounded-[6px] bg-stone-50 border border-stone-200">
                            @foreach ($outlets as $outl)
                                <label class="flex items-center gap-2 text-xs text-stone-700 cursor-pointer">
                                    <input type="checkbox" 
                                           name="outlet_ids[]" 
                                           value="{{ $outl->id }}" 
                                           x-model="selectedOutlets" 
                                           class="rounded-[4px] border-stone-300 text-[#EA580C] focus:ring-[#EA580C]">
                                    <span>{{ $outl->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active_toggle" 
                               value="1" 
                               x-model="isActive" 
                               class="rounded-[4px] border-stone-300 text-[#EA580C] focus:ring-[#EA580C]">
                        <label for="is_active_toggle" class="text-xs font-semibold text-stone-700 cursor-pointer">
                            Akun Aktif (Dapat Login)
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="pt-2 border-t border-stone-100 flex items-center justify-end gap-2">
                        <button type="button" 
                                @click="userModalOpen = false" 
                                class="px-3 py-1.5 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs transition">
                            <span x-text="isEditing ? 'Simpan Perubahan' : 'Buat Pengguna'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
