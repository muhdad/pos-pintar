<x-app-layout>
    @section('page_title', 'Manajemen Pengguna')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">✕</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold p-1">✕</button>
        </div>
    @endif

    <!-- Alpine.js Container for Modal State -->
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
            this.selectedOutlets = [{{ $outlets->first()->id ?? 'null' }}];
            this.userModalOpen = true;
        },

        openEditModal(user) {
            this.isEditing = true;
            this.modalTitle = 'Edit Data Pengguna';
            this.formAction = '/backoffice/users/' + user.id;
            this.userId = user.id;
            this.name = user.name;
            this.email = user.email;
            this.phone = user.phone || '';
            this.role = user.role;
            this.password = '';
            this.pin = '';
            this.isActive = Boolean(user.is_active);
            this.selectedOutlets = user.outlets.map(o => o.id);
            this.userModalOpen = true;
        }
    }" class="space-y-6">

        <!-- Header Row -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/60">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-50 border border-indigo-200/60 text-[10px] font-bold text-indigo-700 uppercase tracking-wider">
                        <span>Hak Akses RBAC</span>
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs text-slate-500 font-medium">Manajemen Akun Staf & Kasir</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Manajemen Pengguna & Staf
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kelola akun Superadmin, Owner, Manager, dan Kasir beserta PIN akses terminal POS.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" 
                        @click="openCreateModal()"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs tracking-wide shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Pengguna Baru</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('backoffice.users.index') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama, email, nomor HP..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-1.5 pl-9 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select name="role" 
                        onchange="this.form.submit()" 
                        class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="">Semua Peran</option>
                    <option value="SUPERADMIN" {{ request('role') === 'SUPERADMIN' ? 'selected' : '' }}>Superadmin</option>
                    <option value="OWNER" {{ request('role') === 'OWNER' ? 'selected' : '' }}>Owner</option>
                    <option value="MANAGER" {{ request('role') === 'MANAGER' ? 'selected' : '' }}>Manager</option>
                    <option value="CASHIER" {{ request('role') === 'CASHIER' ? 'selected' : '' }}>Kasir (Cashier)</option>
                </select>

                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Filter
                </button>

                @if(request('search') || request('role'))
                    <a href="{{ route('backoffice.users.index') }}" class="px-2 py-1.5 text-xs text-rose-600 hover:underline">
                        Reset
                    </a>
                @endif
            </form>

            <span class="text-xs text-slate-400">
                Total: <strong>{{ $users->total() }}</strong> pengguna
            </span>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100 bg-slate-50/50">
                            <th class="py-3 px-6">Pengguna & Akun</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Outlet / Cabang</th>
                            <th class="py-3 px-4 text-center">PIN Kasir (POS)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- User Info -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 {{ $user->role === 'SUPERADMIN' ? 'bg-purple-100 text-purple-700' : ($user->role === 'OWNER' ? 'bg-blue-100 text-blue-700' : ($user->role === 'MANAGER' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')) }}">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-slate-900">{{ $user->name }}</h4>
                                                @if($user->id === auth()->id())
                                                    <span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">Anda</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-slate-500 block">{{ $user->email }}</span>
                                            @if($user->phone)
                                                <span class="text-[10px] text-slate-400 font-mono">{{ $user->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge -->
                                <td class="py-4 px-4">
                                    @if ($user->role === 'SUPERADMIN')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                                            👑 Superadmin
                                        </span>
                                    @elseif ($user->role === 'OWNER')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            👔 Owner
                                        </span>
                                    @elseif ($user->role === 'MANAGER')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            📋 Manager
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            🛒 Kasir
                                        </span>
                                    @endif
                                </td>

                                <!-- Outlets -->
                                <td class="py-4 px-4 text-slate-600">
                                    @if($user->outlets->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($user->outlets as $outl)
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-medium">
                                                    {{ $outl->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Semua Cabang</span>
                                    @endif
                                </td>

                                <!-- PIN Status -->
                                <td class="py-4 px-4 text-center">
                                    @if($user->pin_hash)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span>✓</span>
                                            <span>4-Digit Aktif</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400">Belum diatur</span>
                                    @endif
                                </td>

                                <!-- Status Aktif -->
                                <td class="py-4 px-4 text-center">
                                    <form method="POST" action="{{ route('backoffice.users.toggle', $user->id) }}">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk ubah status" 
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold transition {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                            {{ $user->is_active ? '● Aktif' : '○ Nonaktif' }}
                                        </button>
                                    </form>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="openEditModal({{ json_encode($user) }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-700 border border-slate-200/80 font-bold text-xs transition flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            <span>Edit</span>
                                        </button>

                                        <!-- Delete Button -->
                                        @if($user->id !== auth()->id())
                                            <form method="POST" 
                                                  action="{{ route('backoffice.users.destroy', $user->id) }}" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini? Tindakan ini tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" 
                                                        title="Hapus Pengguna">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    Belum ada pengguna terdaftar. Klik tombol "+ Tambah Pengguna Baru" di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Create / Edit User Modal -->
        <div x-show="userModalOpen" 
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="userModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900" x-text="modalTitle"></h3>
                        <p class="text-xs text-slate-500">Kelola akun dan kredensial login staf kasir & manajer.</p>
                    </div>
                    <button type="button" @click="userModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold p-1">✕</button>
                </div>

                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               x-model="name" 
                               required 
                               placeholder="Contoh: Rian Pratama" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Email & No. HP Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" 
                                   name="email" 
                                   x-model="email" 
                                   required 
                                   placeholder="kasir@tokokita.com" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" 
                                   name="phone" 
                                   x-model="phone" 
                                   placeholder="08123456789" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Peran (Role) & PIN Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Peran Akses (Role) <span class="text-rose-500">*</span></label>
                            <select name="role" 
                                    x-model="role" 
                                    required 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="CASHIER">Kasir (Terminal POS)</option>
                                <option value="MANAGER">Manager (Operasional & Stok)</option>
                                <option value="OWNER">Owner (Backoffice Penuh)</option>
                                <option value="SUPERADMIN">Superadmin (Seluruh Akses)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                4-Digit PIN Kasir
                                <span class="text-[10px] text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <input type="password" 
                                   name="pin" 
                                   x-model="pin" 
                                   maxlength="4" 
                                   placeholder="4 angka (misal: 1024)" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Password Login Web
                            <span x-show="isEditing" class="text-[10px] text-slate-400 font-normal">(kosongkan jika tidak ingin mengubah)</span>
                            <span x-show="!isEditing" class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               x-model="password" 
                               :required="!isEditing" 
                               minlength="6" 
                               placeholder="Minimal 6 karakter" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Cabang Outlet Penugasan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Penugasan Cabang / Outlet</label>
                        <div class="space-y-1.5 max-h-28 overflow-y-auto p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            @foreach ($outlets as $outl)
                                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                    <input type="checkbox" 
                                           name="outlet_ids[]" 
                                           value="{{ $outl->id }}" 
                                           :checked="selectedOutlets.includes('{{ $outl->id }}')" 
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $outl->name }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $outl->address ?? 'Lokasi Utama' }})</span>
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
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active_toggle" class="text-xs font-bold text-slate-700 cursor-pointer">
                            Akun Aktif (Dapat Login & Membuka Terminal)
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="userModalOpen = false" 
                                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/25 transition">
                            <span x-text="isEditing ? 'Simpan Perubahan' : 'Buat Pengguna'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

