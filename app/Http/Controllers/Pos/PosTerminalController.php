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

        // Gunakan tenant pengguna atau tenant pertama (Kopi Senja)
        $tenant = $user?->tenant ?? Tenant::first() ?? Tenant::create([
            'name' => 'Kopi Senja Nusantara',
            'slug' => 'kopi-senja',
            'owner_phone' => '081299887766',
            'plan_tier' => 'PRO_BUNDLE',
            'is_active' => true,
        ]);

        $outlet = $tenant->outlets()->first() ?? Outlet::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cabang Kemang'],
            [
                'type' => 'STORE',
                'address' => 'Jl. Kemang Raya No. 45, Jakarta Selatan',
            ]
        );

        $shift = Shift::where('outlet_id', $outlet->id)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        // Ambil data produk & kategori tenant aktif
        $categories = Category::where('tenant_id', $tenant->id)->get(['id', 'name', 'icon']);

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with(['recipes.rawMaterial'])
            ->get(['id', 'category_id', 'sku', 'barcode', 'name', 'selling_price', 'cogs', 'image_url', 'track_stock']);

        return view('pos.terminal', compact('tenant', 'outlet', 'shift', 'categories', 'products'));
    }
}
