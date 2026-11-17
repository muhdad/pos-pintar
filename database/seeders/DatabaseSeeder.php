<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Recipe;
use App\Models\Tenant;
use App\Models\Terminal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Mengisi data riil spesifik untuk Brand Kuliner:
     * SLL - SOTO LAMONGAN LESTARI
     */
    public function run(): void
    {
        // 1. Tenant SLL - Soto Lamongan Lestari
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'soto-lamongan-lestari'],
            [
                'name' => 'Soto Lamongan Lestari (SLL)',
                'owner_phone' => '085213254219',
                'plan_tier' => 'ENTERPRISE',
                'is_active' => true,
            ]
        );

        // 2. Outlet Cabang Utama
        $outlet = Outlet::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'SLL Cabang Utama'],
            [
                'type' => 'STORE',
                'address' => 'Pusat Kuliner Lamongan Lestari, Indonesia',
                'phone' => '+62 852-1325-4219',
            ]
        );

        // 3. Terminal Kasir Utama
        Terminal::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Kasir Utama SLL 01'],
            ['is_active' => true]
        );

        // 4. Akun Superadmin Tunggal
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@pospintar.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Owner SLL (Superadmin)',
                'password' => Hash::make('admin123'),
                'pin_hash' => Hash::make('1234'),
                'role' => 'SUPERADMIN',
                'phone' => '085213254219',
                'is_active' => true,
            ]
        );

        // Hubungkan Superadmin ke Cabang Utama
        $outlet->users()->syncWithoutDetaching([$superadmin->id]);

        // 5. Kategori Menu SLL (Sesuai Brosur Menu)
        $catSoto = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Soto'],
            ['icon' => 'soup']
        );

        $catMieBakso = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Mie Ayam & Bakso'],
            ['icon' => 'noodles']
        );

        $catTambahan = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Menu Tambahan'],
            ['icon' => 'extra']
        );

        $catMinuman = Category::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Minuman'],
            ['icon' => 'drink']
        );

        // 6. Bahan Baku Utama Gudang (Raw Materials)
        $rawMaterials = [
            'ayam_potong' => ['name' => 'Daging Ayam Suwir & Tulang', 'unit' => 'gram', 'cost' => 45, 'min' => 2000, 'stock' => 15000],
            'daging_sapi' => ['name' => 'Daging Sapi Pilihan', 'unit' => 'gram', 'cost' => 120, 'min' => 1000, 'stock' => 10000],
            'beras'       => ['name' => 'Beras Pulen', 'unit' => 'gram', 'cost' => 15, 'min' => 5000, 'stock' => 25000],
            'mie_basah'   => ['name' => 'Mie Basah Segar', 'unit' => 'gram', 'cost' => 25, 'min' => 1500, 'stock' => 10000],
            'bakso_sapi'  => ['name' => 'Pentol Bakso Daging', 'unit' => 'pcs', 'cost' => 1500, 'min' => 100, 'stock' => 500],
            'telur_ayam'  => ['name' => 'Telur Ayam Rebus', 'unit' => 'butir', 'cost' => 2000, 'min' => 50, 'stock' => 200],
            'koya_bumbu'  => ['name' => 'Koya Gurih Spesial SLL', 'unit' => 'gram', 'cost' => 30, 'min' => 500, 'stock' => 5000],
            'kepala_ayam' => ['name' => 'Kepala Ayam Ungkep', 'unit' => 'pcs', 'cost' => 3500, 'min' => 20, 'stock' => 100],
            'sayap_ayam'  => ['name' => 'Sayap Ayam Ungkep', 'unit' => 'pcs', 'cost' => 4000, 'min' => 20, 'stock' => 100],
            'ati_ampela'  => ['name' => 'Ati Ampela Bumbu', 'unit' => 'pcs', 'cost' => 4000, 'min' => 20, 'stock' => 100],
            'ceker_ayam'  => ['name' => 'Ceker Ayam Lembut', 'unit' => 'pcs', 'cost' => 1500, 'min' => 30, 'stock' => 150],
            'kulit_ayam'  => ['name' => 'Kulit Ayam Goreng', 'unit' => 'gram', 'cost' => 35, 'min' => 500, 'stock' => 2000],
            'jeruk_peras' => ['name' => 'Jeruk Manis Segar', 'unit' => 'pcs', 'cost' => 1500, 'min' => 50, 'stock' => 300],
            'daun_teh'    => ['name' => 'Teh Wangi Melati', 'unit' => 'gram', 'cost' => 40, 'min' => 200, 'stock' => 1500],
            'gula_pasir'  => ['name' => 'Gula Pasir Kristal', 'unit' => 'gram', 'cost' => 18, 'min' => 1000, 'stock' => 10000],
        ];

        $rmInstances = [];
        foreach ($rawMaterials as $key => $rm) {
            $material = RawMaterial::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $rm['name']],
                [
                    'sku' => 'RM-' . strtoupper($key),
                    'unit' => $rm['unit'],
                    'average_cost' => $rm['cost'],
                    'minimum_stock' => $rm['min'],
                ]
            );
            $rmInstances[$key] = $material;

            OutletStock::firstOrCreate(
                ['outlet_id' => $outlet->id, 'raw_material_id' => $material->id],
                ['current_stock' => $rm['stock']]
            );
        }

        // 7. Daftar Menu Lengkap SLL (Berdasarkan Gambar Menu SLL)
        $menuList = [
            // --- Kategori SOTO ---
            [
                'category_id'   => $catSoto->id,
                'name'          => 'Soto Ayam',
                'selling_price' => 15000,
                'cogs'          => 7500,
                'sku'           => 'SLL-SOTO-AYAM',
                'recipes'       => [
                    ['material' => 'ayam_potong', 'qty' => 100],
                    ['material' => 'koya_bumbu',  'qty' => 15],
                ]
            ],
            [
                'category_id'   => $catSoto->id,
                'name'          => 'Soto Ayam Campur Nasi',
                'selling_price' => 17000,
                'cogs'          => 9000,
                'sku'           => 'SLL-SOTO-AYAM-NASI',
                'recipes'       => [
                    ['material' => 'ayam_potong', 'qty' => 100],
                    ['material' => 'beras',       'qty' => 100],
                    ['material' => 'koya_bumbu',  'qty' => 15],
                ]
            ],
            [
                'category_id'   => $catSoto->id,
                'name'          => 'Soto Daging',
                'selling_price' => 23000,
                'cogs'          => 12500,
                'sku'           => 'SLL-SOTO-DAGING',
                'recipes'       => [
                    ['material' => 'daging_sapi', 'qty' => 90],
                    ['material' => 'koya_bumbu',  'qty' => 15],
                ]
            ],
            [
                'category_id'   => $catSoto->id,
                'name'          => 'Soto Daging Campur Nasi',
                'selling_price' => 25000,
                'cogs'          => 14000,
                'sku'           => 'SLL-SOTO-DAGING-NASI',
                'recipes'       => [
                    ['material' => 'daging_sapi', 'qty' => 90],
                    ['material' => 'beras',       'qty' => 100],
                    ['material' => 'koya_bumbu',  'qty' => 15],
                ]
            ],
            [
                'category_id'   => $catSoto->id,
                'name'          => 'Nasi Putih',
                'selling_price' => 5000,
                'cogs'          => 2000,
                'sku'           => 'SLL-NASI-PUTIH',
                'recipes'       => [
                    ['material' => 'beras', 'qty' => 120],
                ]
            ],

            // --- Kategori MIE AYAM & BAKSO ---
            [
                'category_id'   => $catMieBakso->id,
                'name'          => 'Mie Ayam',
                'selling_price' => 12000,
                'cogs'          => 5500,
                'sku'           => 'SLL-MIE-AYAM',
                'recipes'       => [
                    ['material' => 'mie_basah',   'qty' => 120],
                    ['material' => 'ayam_potong', 'qty' => 50],
                ]
            ],
            [
                'category_id'   => $catMieBakso->id,
                'name'          => 'Mie Ayam Bakso',
                'selling_price' => 17000,
                'cogs'          => 8500,
                'sku'           => 'SLL-MIE-AYAM-BAKSO',
                'recipes'       => [
                    ['material' => 'mie_basah',   'qty' => 120],
                    ['material' => 'ayam_potong', 'qty' => 50],
                    ['material' => 'bakso_sapi',  'qty' => 2],
                ]
            ],
            [
                'category_id'   => $catMieBakso->id,
                'name'          => 'Bakso Biasa',
                'selling_price' => 15000,
                'cogs'          => 7500,
                'sku'           => 'SLL-BAKSO-BIASA',
                'recipes'       => [
                    ['material' => 'bakso_sapi', 'qty' => 5],
                ]
            ],
            [
                'category_id'   => $catMieBakso->id,
                'name'          => 'Bakso Urat',
                'selling_price' => 20000,
                'cogs'          => 10000,
                'sku'           => 'SLL-BAKSO-URAT',
                'recipes'       => [
                    ['material' => 'bakso_sapi',  'qty' => 4],
                    ['material' => 'daging_sapi', 'qty' => 30],
                ]
            ],
            [
                'category_id'   => $catMieBakso->id,
                'name'          => 'Bakso Telur',
                'selling_price' => 20000,
                'cogs'          => 10500,
                'sku'           => 'SLL-BAKSO-TELUR',
                'recipes'       => [
                    ['material' => 'bakso_sapi', 'qty' => 3],
                    ['material' => 'telur_ayam', 'qty' => 1],
                ]
            ],

            // --- Kategori MENU TAMBAHAN ---
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Kepala Ayam',
                'selling_price' => 8000,
                'cogs'          => 3500,
                'sku'           => 'SLL-EXTRA-KEPALA',
                'recipes'       => [['material' => 'kepala_ayam', 'qty' => 1]]
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Sayap Ayam',
                'selling_price' => 8000,
                'cogs'          => 4000,
                'sku'           => 'SLL-EXTRA-SAYAP',
                'recipes'       => [['material' => 'sayap_ayam', 'qty' => 1]]
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Ati Ampela',
                'selling_price' => 8000,
                'cogs'          => 4000,
                'sku'           => 'SLL-EXTRA-ATIAMPELA',
                'recipes'       => [['material' => 'ati_ampela', 'qty' => 1]]
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Ceker Ayam',
                'selling_price' => 5000,
                'cogs'          => 2000,
                'sku'           => 'SLL-EXTRA-CEKER',
                'recipes'       => [['material' => 'ceker_ayam', 'qty' => 2]]
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Kulit Ayam',
                'selling_price' => 5000,
                'cogs'          => 2500,
                'sku'           => 'SLL-EXTRA-KULIT',
                'recipes'       => [['material' => 'kulit_ayam', 'qty' => 50]]
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Telur Muda (Uritan)',
                'selling_price' => 5000,
                'cogs'          => 2500,
                'sku'           => 'SLL-EXTRA-TELUR-MUDA',
                'recipes'       => []
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Telur Bulat Rebus',
                'selling_price' => 5000,
                'cogs'          => 2200,
                'sku'           => 'SLL-EXTRA-TELUR-BULAT',
                'recipes'       => [['material' => 'telur_ayam', 'qty' => 1]]
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Kerupuk Udang',
                'selling_price' => 5000,
                'cogs'          => 2000,
                'sku'           => 'SLL-EXTRA-KERUPUK-UDANG',
                'recipes'       => []
            ],
            [
                'category_id'   => $catTambahan->id,
                'name'          => 'Peyek Gurih',
                'selling_price' => 7000,
                'cogs'          => 3000,
                'sku'           => 'SLL-EXTRA-PEYEK',
                'recipes'       => []
            ],

            // --- Kategori MINUMAN ---
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Es Jeruk',
                'selling_price' => 10000,
                'cogs'          => 3500,
                'sku'           => 'SLL-MINUM-ES-JERUK',
                'recipes'       => [
                    ['material' => 'jeruk_peras', 'qty' => 2],
                    ['material' => 'gula_pasir',  'qty' => 25],
                ]
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Jeruk Hangat',
                'selling_price' => 10000,
                'cogs'          => 3500,
                'sku'           => 'SLL-MINUM-JERUK-HANGAT',
                'recipes'       => [
                    ['material' => 'jeruk_peras', 'qty' => 2],
                    ['material' => 'gula_pasir',  'qty' => 25],
                ]
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Es Teh Manis',
                'selling_price' => 5000,
                'cogs'          => 1500,
                'sku'           => 'SLL-MINUM-ES-TEH-MANIS',
                'recipes'       => [
                    ['material' => 'daun_teh',   'qty' => 5],
                    ['material' => 'gula_pasir', 'qty' => 20],
                ]
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Teh Manis Hangat',
                'selling_price' => 5000,
                'cogs'          => 1500,
                'sku'           => 'SLL-MINUM-TEH-MANIS-HANGAT',
                'recipes'       => [
                    ['material' => 'daun_teh',   'qty' => 5],
                    ['material' => 'gula_pasir', 'qty' => 20],
                ]
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Es Teh Tawar',
                'selling_price' => 3000,
                'cogs'          => 800,
                'sku'           => 'SLL-MINUM-ES-TEH-TAWAR',
                'recipes'       => [
                    ['material' => 'daun_teh', 'qty' => 5],
                ]
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Teh Tawar Hangat',
                'selling_price' => 2000,
                'cogs'          => 600,
                'sku'           => 'SLL-MINUM-TEH-TAWAR',
                'recipes'       => [
                    ['material' => 'daun_teh', 'qty' => 5],
                ]
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Air Mineral',
                'selling_price' => 5000,
                'cogs'          => 2500,
                'sku'           => 'SLL-MINUM-AIR-MINERAL',
                'recipes'       => []
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Teh Pucuk',
                'selling_price' => 5000,
                'cogs'          => 3000,
                'sku'           => 'SLL-MINUM-TEH-PUCUK',
                'recipes'       => []
            ],
            [
                'category_id'   => $catMinuman->id,
                'name'          => 'Es Batu Kristal',
                'selling_price' => 2000,
                'cogs'          => 500,
                'sku'           => 'SLL-MINUM-ES-BATU',
                'recipes'       => []
            ],
        ];

        foreach ($menuList as $m) {
            // Assign AI culinary image based on product type
            $img = '/images/products/soto-ayam.jpg';
            $nameLower = strtolower($m['name']);
            if (str_contains($nameLower, 'soto daging')) {
                $img = '/images/products/soto-daging.jpg';
            } elseif (str_contains($nameLower, 'soto')) {
                $img = '/images/products/soto-ayam.jpg';
            } elseif (str_contains($nameLower, 'mie')) {
                $img = '/images/products/mie-ayam.jpg';
            } elseif (str_contains($nameLower, 'bakso')) {
                $img = '/images/products/bakso.jpg';
            } elseif (str_contains($nameLower, 'ceker')) {
                $img = '/images/products/ceker.jpg';
            } elseif (str_contains($nameLower, 'jeruk')) {
                $img = '/images/products/es-jeruk.jpg';
            } elseif (str_contains($nameLower, 'teh')) {
                $img = '/images/products/es-teh.jpg';
            } elseif ($m['category_id'] === $catTambahan->id) {
                $img = '/images/products/lauk.jpg';
            } elseif ($m['category_id'] === $catMinuman->id) {
                $img = '/images/products/es-teh.jpg';
            }

            $product = Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $m['name']],
                [
                    'category_id'   => $m['category_id'],
                    'sku'           => $m['sku'],
                    'selling_price' => $m['selling_price'],
                    'cogs'          => $m['cogs'],
                    'image_url'     => $img,
                    'is_active'     => true,
                ]
            );

            foreach ($m['recipes'] as $rec) {
                if (isset($rmInstances[$rec['material']])) {
                    Recipe::firstOrCreate(
                        [
                            'product_id'      => $product->id,
                            'raw_material_id' => $rmInstances[$rec['material']]->id,
                        ],
                        [
                            'quantity' => $rec['qty'],
                        ]
                    );
                }
            }
        }
    }
}
