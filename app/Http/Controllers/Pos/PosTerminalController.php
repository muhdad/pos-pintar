<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosTerminalController extends Controller
{
    /**
     * Tampilkan layar utama terminal kasir POS.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Gunakan tenant pengguna atau tenant pertama (SLL)
        $tenant = $user?->tenant ?? Tenant::first() ?? Tenant::create([
            'name' => 'Soto Lamongan Lestari (SLL)',
            'slug' => 'sll-lestari',
            'owner_phone' => '+62 852-1325-4219',
            'plan_tier' => 'PRO_BUNDLE',
            'is_active' => true,
        ]);

        $outlet = $tenant->outlets()->first() ?? Outlet::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'SLL Cabang Utama'],
            [
                'type' => 'RESTO',
                'address' => 'Jl. Raya Lamongan No. 12',
                'phone' => '+62 852-1325-4219',
            ]
        );

        $shift = Shift::where('outlet_id', $outlet->id)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        // Ambil atau inisialisasi Meja Resto untuk Dine-In
        $tables = \App\Models\Table::where('outlet_id', $outlet->id)->orderBy('name')->get();
        if ($tables->isEmpty()) {
            for ($i = 1; $i <= 12; $i++) {
                $tables->push(\App\Models\Table::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'outlet_id' => $outlet->id,
                    'name' => sprintf('Meja %02d', $i),
                    'is_occupied' => false,
                ]));
            }
        }

        // Ambil daftar Open Bill aktif yang belum dibayar
        $activeOpenBills = \App\Models\Order::where('outlet_id', $outlet->id)
            ->whereIn('status', ['PENDING', 'HOLD'])
            ->with(['items.product', 'table'])
            ->latest()
            ->get();

        // Ambil data stok bahan baku outlet untuk pengecekan resep
        $stocks = \App\Models\OutletStock::where('outlet_id', $outlet->id)
            ->get(['id', 'raw_material_id', 'current_stock']);

        // Ambil data produk & kategori tenant aktif
        $categories = Category::where('tenant_id', $tenant->id)->get(['id', 'name', 'icon']);

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with(['recipes.rawMaterial'])
            ->get(['id', 'category_id', 'sku', 'barcode', 'name', 'selling_price', 'cogs', 'image_url', 'track_stock', 'is_active']);

        return view('pos.terminal', compact('tenant', 'outlet', 'shift', 'tables', 'activeOpenBills', 'categories', 'products', 'stocks'));
    }

    /**
     * Tampilkan Layar Pelanggan (Customer Facing Display) Fullscreen untuk QRIS Dinamis & Struk Pesanan.
     */
    public function customerDisplay(Request $request): View
    {
        $user = $request->user();
        $tenant = $user?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        return view('pos.customer-display', compact('tenant', 'outlet'));
    }
}
