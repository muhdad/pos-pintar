<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\Terminal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Hanya membuat struktur dasar (Tenant, Outlet, Terminal) dan 1 Akun Superadmin.
     * Seluruh data produk, kategori, resep, dan bahan baku diinput langsung melalui antarmuka web.
     */
    public function run(): void
    {
        // 1. Tenant Dasar
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'pos-pintar'],
            [
                'name' => 'POS Pintar Store',
                'owner_phone' => '081234567890',
                'plan_tier' => 'ENTERPRISE',
                'is_active' => true,
            ]
        );

        // 2. Outlet / Cabang Utama
        $outlet = Outlet::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cabang Utama'],
            [
                'type' => 'STORE',
                'address' => 'Jl. Jenderal Sudirman No. 1, Jakarta Pusat',
                'phone' => '021-5551234',
            ]
        );

        // 3. Terminal Kasir Utama
        Terminal::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Kasir Utama 01'],
            ['is_active' => true]
        );

        // 4. Akun Superadmin Tunggal
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@pospintar.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'pin_hash' => Hash::make('1234'),
                'role' => 'SUPERADMIN',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        // Hubungkan Superadmin ke Cabang Utama
        $outlet->users()->syncWithoutDetaching([$superadmin->id]);
    }
}
