<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\Terminal;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-dummy-data {--keep-users : Jangan hapus user yang sudah dibuat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan seluruh data dummy (transaksi, produk, bahan baku, stok) untuk persiapan data riil';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pembersihan data dummy sistem POS...');

        // Matikan sementara foreign key check agar truncate / delete aman
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 1. Hapus Seluruh Transaksi Penjualan & Struk Dummy
            $this->line('1. Menghapus riwayat transaksi, pembayaran, dan order items...');
            DB::table('order_items')->truncate();
            DB::table('payments')->truncate();
            DB::table('orders')->truncate();
            DB::table('shifts')->truncate();
            DB::table('audit_logs')->truncate();
            DB::table('ai_daily_reports')->truncate();

            // 2. Hapus Data Resep, Gudang, Mutasi & Bahan Baku Dummy
            $this->line('2. Menghapus resep (BOM), mutasi stok, dan bahan baku dummy...');
            DB::table('stock_movements')->truncate();
            DB::table('outlet_stocks')->truncate();
            DB::table('recipes')->truncate();
            DB::table('products')->truncate();
            DB::table('raw_materials')->truncate();

            // 3. Reset Status Meja
            $this->line('3. Mereset status Meja Resto...');
            DB::table('tables')->update([
                'is_occupied' => false,
                'current_order_id' => null,
            ]);

            // 4. Pastikan Tenant & Outlet SLL tetap ada
            $this->line('4. Memastikan Profil Usaha Soto Lamongan Lestari (SLL)...');
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'soto-lamongan-lestari'],
                [
                    'name' => 'Soto Lamongan Lestari (SLL)',
                    'owner_phone' => '085213254219',
                    'plan_tier' => 'ENTERPRISE',
                    'is_active' => true,
                ]
            );

            $outlet = Outlet::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'SLL Cabang Utama'],
                [
                    'type' => 'STORE',
                    'address' => 'Pusat Kuliner Lamongan Lestari, Indonesia',
                    'phone' => '+62 852-1325-4219',
                ]
            );

            Terminal::firstOrCreate(
                ['outlet_id' => $outlet->id, 'name' => 'Kasir Utama SLL 01'],
                ['is_active' => true]
            );

            // Inisialisasi Meja 01 s/d Meja 12 jika belum ada
            if (Table::where('outlet_id', $outlet->id)->count() === 0) {
                for ($i = 1; $i <= 12; $i++) {
                    Table::create([
                        'id' => (string) Str::uuid(),
                        'outlet_id' => $outlet->id,
                        'name' => sprintf('Meja %02d', $i),
                        'is_occupied' => false,
                    ]);
                }
            }

            // 5. Reset Kategori Bersih Sesuai Brosur Banner SLL
            $this->line('5. Menyiapkan 4 Kategori Menu Utama Sesuai Brosur SLL...');
            DB::table('categories')->truncate();
            
            $categories = [
                ['name' => 'Soto', 'icon' => 'soup'],
                ['name' => 'Mie Ayam & Bakso', 'icon' => 'noodles'],
                ['name' => 'Menu Tambahan', 'icon' => 'extra'],
                ['name' => 'Minuman', 'icon' => 'drink'],
            ];

            foreach ($categories as $cat) {
                Category::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                ]);
            }

            // 6. Pastikan Akun Superadmin Aktif
            if (User::where('tenant_id', $tenant->id)->count() === 0) {
                $superadmin = User::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'name' => 'Owner SLL (Superadmin)',
                    'email' => 'admin@pospintar.com',
                    'password' => Hash::make('admin123'),
                    'pin_hash' => Hash::make('1234'),
                    'role' => 'SUPERADMIN',
                    'phone' => '085213254219',
                    'is_active' => true,
                ]);
                $outlet->users()->syncWithoutDetaching([$superadmin->id]);
            }

            $this->info('✓ SELURUH DATA DUMMY BERHASIL DIBERSIHKAN!');
            $this->info('  - Katalog Produk & Bahan Baku: KOSONG BERSIH (Siap diinput data riil)');
            $this->info('  - Riwayat Penjualan & Transaksi: KOSONG BERSIH (Omzet mulai dari 0)');
            $this->info('  - Kategori Siap Pakai: Soto, Mie Ayam & Bakso, Menu Tambahan, Minuman');
            $this->info('  - Meja Resto: Meja 01 s/d Meja 12 dalam kondisi Kosong');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal membersihkan data dummy: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
