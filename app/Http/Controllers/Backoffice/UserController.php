<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna/karyawan.
     */
    public function index(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $query = User::with('outlets');

        // Jika bukan superadmin, batasi ke tenant aktif
        if (! $request->user()?->isSuperAdmin() && $tenant) {
            $query->where('tenant_id', $tenant->id);
        }

        // Filter pencarian nama / email / nomor hp
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(15);
        $outlets = $tenant ? $tenant->outlets : Outlet::all();

        return view('backoffice.users.index', compact('users', 'outlets', 'tenant'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|in:SUPERADMIN,OWNER,MANAGER,CASHIER',
            'password' => 'required|string|min:6',
            'pin' => 'nullable|string|digits:4',
            'outlet_ids' => 'nullable|array',
            'outlet_ids.*' => 'exists:outlets,id',
            'is_active' => 'nullable',
        ]);

        $user = User::create([
            'tenant_id' => $tenant?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'pin_hash' => ! empty($validated['pin']) ? Hash::make($validated['pin']) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['outlet_ids'])) {
            $user->outlets()->sync($validated['outlet_ids']);
        } elseif ($tenant && $tenant->outlets()->exists()) {
            // Default hubungkan ke outlet pertama jika tidak dipilih
            $user->outlets()->sync([$tenant->outlets()->first()->id]);
        }

        return redirect()->route('backoffice.users.index')
            ->with('status', "Pengguna {$user->name} berhasil ditambahkan!");
    }

    /**
     * Perbarui data pengguna yang sudah ada.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|in:SUPERADMIN,OWNER,MANAGER,CASHIER',
            'password' => 'nullable|string|min:6',
            'pin' => 'nullable|string|digits:4',
            'outlet_ids' => 'nullable|array',
            'outlet_ids.*' => 'exists:outlets,id',
            'is_active' => 'nullable',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if (! empty($validated['pin'])) {
            $userData['pin_hash'] = Hash::make($validated['pin']);
        }

        $user->update($userData);

        if (isset($validated['outlet_ids'])) {
            $user->outlets()->sync($validated['outlet_ids']);
        }

        return redirect()->route('backoffice.users.index')
            ->with('status', "Data pengguna {$user->name} berhasil diperbarui!");
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan!');
        }

        $userName = $user->name;
        $user->outlets()->detach();
        $user->delete();

        return redirect()->route('backoffice.users.index')
            ->with('status', "Akun pengguna {$userName} berhasil dihapus.");
    }

    /**
     * Toggle status aktif/nonaktif akun.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri!');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Akun {$user->name} berhasil {$statusStr}.");
    }
}

