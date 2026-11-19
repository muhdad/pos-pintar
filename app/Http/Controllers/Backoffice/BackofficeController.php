<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AiDailyReport;
use App\Models\Category;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Recipe;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Services\Ai\AiConsultantService;
use App\Services\Ai\BusinessDataAggregator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BackofficeController extends Controller
{
    public function __construct(
        protected BusinessDataAggregator $aggregator,
        protected AiConsultantService $consultantService
    ) {}

    /**
     * Dashboard Utama Backoffice & Ringkasan Metrik Harian.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $tenant = $user?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $todayStr = date('Y-m-d');

        // Agregasi metrik bisnis hari ini untuk seluruh cabang tenant
        $metrics = $this->aggregator->aggregateDailyMetrics($tenant->id, null, $todayStr);

        // Ambil atau hasilkan AI Report untuk hari ini agar selalu sinkron dengan omzet real-time
        $latestAiReport = AiDailyReport::where('tenant_id', $tenant->id)
            ->where('report_date', $todayStr)
            ->whereNull('outlet_id')
            ->first();

        if (! $latestAiReport) {
            $latestAiReport = $this->consultantService->generateDailyReport($tenant->id, null, $todayStr);
        }

        $recentOrders = Order::where('tenant_id', $tenant->id)
            ->with(['items.product', 'user', 'payments'])
            ->latest()
            ->limit(10)
            ->get();

        $lowStockItems = OutletStock::where('outlet_id', $outlet->id)
            ->whereHas('rawMaterial', function ($q) {
                $q->whereColumn('outlet_stocks.current_stock', '<=', 'raw_materials.minimum_stock');
            })
            ->with('rawMaterial')
            ->get();

        $totalProductsCount = Product::where('tenant_id', $tenant->id)->count();
        $totalRawMaterialsCount = RawMaterial::where('tenant_id', $tenant->id)->count();

        return view('dashboard', compact(
            'tenant',
            'outlet',
            'metrics',
            'latestAiReport',
            'recentOrders',
            'lowStockItems',
            'totalProductsCount',
            'totalRawMaterialsCount'
        ));
    }

    /**
     * Manajemen Katalog Produk & Resep (BOM).
     */
    public function products(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $categories = Category::where('tenant_id', $tenant->id)->get();
        $rawMaterials = RawMaterial::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)
            ->with(['category', 'recipes.rawMaterial'])
            ->latest()
            ->get();

        return view('backoffice.products', compact('tenant', 'categories', 'rawMaterials', 'products'));
    }

    /**
     * Tambah Kategori Produk Baru.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
        ]);

        Category::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? 'coffee',
        ]);

        return back()->with('status', "Kategori '{$validated['name']}' berhasil ditambahkan!");
    }

    /**
     * Perbarui Kategori Produk.
     */
    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
        ]);

        $category->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? $category->icon ?? 'coffee',
        ]);

        return back()->with('status', "Kategori '{$category->name}' berhasil diperbarui!");
    }

    /**
     * Hapus Kategori Produk.
     */
    public function destroyCategory(Category $category): RedirectResponse
    {
        $categoryName = $category->name;
        $category->delete();

        return back()->with('status', "Kategori '{$categoryName}' berhasil dihapus.");
    }

    /**
     * Tambah Menu Produk Baru Beserta Resep (BOM).
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:50',
            'selling_price' => 'required|numeric|min:0',
            'cogs' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string|max:255',
            'recipes' => 'nullable|array',
            'recipes.*.raw_material_id' => 'required|exists:raw_materials,id',
            'recipes.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($tenant, $validated) {
            $product = Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $validated['category_id'],
                'sku' => !empty($validated['sku']) ? $validated['sku'] : 'SKU-' . strtoupper(substr(uniqid(), -6)),
                'name' => $validated['name'],
                'selling_price' => $validated['selling_price'],
                'cogs' => $validated['cogs'] ?? 0,
                'image_url' => $validated['image_url'] ?? null,
                'is_active' => true,
            ]);

            if (! empty($validated['recipes'])) {
                $totalCalculatedCogs = 0;
                foreach ($validated['recipes'] as $item) {
                    Recipe::create([
                        'product_id' => $product->id,
                        'raw_material_id' => $item['raw_material_id'],
                        'quantity' => $item['quantity'],
                    ]);

                    $rawMat = RawMaterial::find($item['raw_material_id']);
                    if ($rawMat) {
                        $totalCalculatedCogs += ($rawMat->average_cost * $item['quantity']);
                    }
                }

                if (empty($validated['cogs']) && $totalCalculatedCogs > 0) {
                    $product->update(['cogs' => $totalCalculatedCogs]);
                }
            }
        });

        return back()->with('status', "Produk '{$validated['name']}' berhasil ditambahkan ke katalog!");
    }

    /**
     * Perbarui Menu Produk Beserta Resep (BOM).
     */
    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:50',
            'selling_price' => 'required|numeric|min:0',
            'cogs' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string|max:255',
            'is_active' => 'nullable',
            'recipes' => 'nullable|array',
            'recipes.*.raw_material_id' => 'required|exists:raw_materials,id',
            'recipes.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($product, $validated, $request) {
            $product->update([
                'category_id' => $validated['category_id'],
                'sku' => !empty($validated['sku']) ? $validated['sku'] : $product->sku,
                'name' => $validated['name'],
                'selling_price' => $validated['selling_price'],
                'cogs' => $validated['cogs'] ?? $product->cogs,
                'image_url' => $validated['image_url'] ?? $product->image_url,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Sync Resep
            $product->recipes()->delete();

            if (! empty($validated['recipes'])) {
                $totalCalculatedCogs = 0;
                foreach ($validated['recipes'] as $item) {
                    Recipe::create([
                        'product_id' => $product->id,
                        'raw_material_id' => $item['raw_material_id'],
                        'quantity' => $item['quantity'],
                    ]);

                    $rawMat = RawMaterial::find($item['raw_material_id']);
                    if ($rawMat) {
                        $totalCalculatedCogs += ($rawMat->average_cost * $item['quantity']);
                    }
                }

                if (empty($validated['cogs']) && $totalCalculatedCogs > 0) {
                    $product->update(['cogs' => $totalCalculatedCogs]);
                }
            }
        });

        return back()->with('status', "Data produk '{$product->name}' berhasil diperbarui!");
    }

    /**
     * Toggle status aktif/nonaktif produk.
     */
    public function toggleProduct(Request $request, Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);
        $statusStr = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Produk '{$product->name}' berhasil {$statusStr}.");
    }

    /**
     * Hapus Menu Produk secara Aman.
     */
    public function destroyProduct(Product $product): RedirectResponse
    {
        $productName = $product->name;

        // Cek jika produk pernah ada di riwayat pesanan (foreign key restrict)
        $hasOrders = DB::table('order_items')->where('product_id', $product->id)->exists();
        if ($hasOrders) {
            $product->update(['is_active' => false]);
            return back()->with('status', "Produk '{$productName}' dinonaktifkan dari katalog (tidak dihapus permanen karena ada riwayat transaksi).");
        }

        $product->recipes()->delete();
        $product->delete();

        return back()->with('status', "Produk '{$productName}' berhasil dihapus.");
    }

    /**
     * Manajemen Bahan Baku & Stok Gudang Cabang.
     */
    public function inventory(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $rawMaterials = RawMaterial::where('tenant_id', $tenant->id)
            ->with(['outletStocks' => function ($q) use ($outlet) {
                $q->where('outlet_id', $outlet->id);
            }])
            ->get();

        $movements = StockMovement::whereHas('outletStock', function ($q) use ($outlet) {
            $q->where('outlet_id', $outlet->id);
        })
        ->with('outletStock.rawMaterial')
        ->latest('created_at')
        ->limit(15)
        ->get();

        return view('backoffice.inventory', compact('tenant', 'outlet', 'rawMaterials', 'movements'));
    }

    /**
     * Tambah Bahan Baku Baru ke Gudang.
     */
    public function storeRawMaterial(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'unit' => 'required|string|max:20',
            'average_cost' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'initial_stock' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($tenant, $outlet, $validated) {
            $material = RawMaterial::create([
                'tenant_id' => $tenant->id,
                'sku' => 'RM-' . strtoupper(substr(uniqid(), -5)),
                'name' => $validated['name'],
                'unit' => $validated['unit'],
                'average_cost' => $validated['average_cost'],
                'minimum_stock' => $validated['minimum_stock'],
            ]);

            $initialStock = $validated['initial_stock'] ?? 0;
            $stock = OutletStock::create([
                'outlet_id' => $outlet->id,
                'raw_material_id' => $material->id,
                'current_stock' => $initialStock,
            ]);

            if ($initialStock > 0) {
                StockMovement::create([
                    'outlet_stock_id' => $stock->id,
                    'type' => 'IN',
                    'quantity_delta' => $initialStock,
                    'balance_after' => $initialStock,
                    'notes' => 'Pendaftaran stok awal bahan baku',
                ]);
            }
        });

        return back()->with('status', "Bahan baku '{$validated['name']}' berhasil didaftarkan ke gudang!");
    }

    /**
     * Perbarui Data Bahan Baku & Koreksi Stok.
     */
    public function updateRawMaterial(Request $request, RawMaterial $rawMaterial): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'unit' => 'required|string|max:20',
            'average_cost' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($rawMaterial, $validated, $request) {
            $rawMaterial->update([
                'name' => $validated['name'],
                'unit' => $validated['unit'],
                'average_cost' => $validated['average_cost'],
                'minimum_stock' => $validated['minimum_stock'],
            ]);

            // Jika ada koreksi stok riil
            if ($request->filled('current_stock')) {
                $tenant = $request->user()?->tenant ?? Tenant::first();
                $outlet = $tenant?->outlets()->first() ?? Outlet::first();

                $stock = OutletStock::firstOrCreate(
                    ['outlet_id' => $outlet->id, 'raw_material_id' => $rawMaterial->id],
                    ['current_stock' => 0]
                );

                $newStock = (float) $validated['current_stock'];
                $diff = $newStock - (float) $stock->current_stock;

                if (abs($diff) > 0.0001) {
                    $stock->update(['current_stock' => $newStock]);

                    StockMovement::create([
                        'outlet_stock_id' => $stock->id,
                        'type' => 'OPNAME_ADJUSTMENT',
                        'quantity_delta' => $diff,
                        'balance_after' => $newStock,
                        'notes' => 'Koreksi stok opname manual',
                    ]);
                }
            }
        });

        return back()->with('status', "Bahan baku '{$rawMaterial->name}' berhasil diperbarui!");
    }

    /**
     * Hapus Bahan Baku dari Gudang.
     */
    public function destroyRawMaterial(RawMaterial $rawMaterial): RedirectResponse
    {
        $name = $rawMaterial->name;

        // Cek jika bahan baku sedang digunakan dalam resep menu
        $usedInRecipes = Recipe::where('raw_material_id', $rawMaterial->id)->count();
        if ($usedInRecipes > 0) {
            return back()->with('error', "Bahan baku '{$name}' tidak dapat dihapus karena masih digunakan dalam {$usedInRecipes} resep menu produk!");
        }

        DB::transaction(function () use ($rawMaterial) {
            $rawMaterial->outletStocks()->delete();
            $rawMaterial->delete();
        });

        return back()->with('status', "Bahan baku '{$name}' berhasil dihapus dari gudang.");
    }

    /**
     * Restock Bahan Baku Gudang.
     */
    public function restockInventory(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $validated = $request->validate([
            'raw_material_id' => 'required|exists:raw_materials,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($outlet, $validated) {
            $stock = OutletStock::firstOrCreate(
                ['outlet_id' => $outlet->id, 'raw_material_id' => $validated['raw_material_id']],
                ['current_stock' => 0]
            );

            $stock->increment('current_stock', $validated['quantity']);

            StockMovement::create([
                'outlet_stock_id' => $stock->id,
                'type' => 'IN',
                'quantity_delta' => $validated['quantity'],
                'balance_after' => $stock->current_stock,
                'notes' => !empty($validated['notes']) ? $validated['notes'] : 'Restock manual gudang',
            ]);
        });

        return back()->with('status', 'Stok bahan baku berhasil ditambahkan!');
    }

    /**
     * Riwayat Transaksi & Struk Kasir.
     */
    public function orders(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $orders = Order::where('tenant_id', $tenant->id)
            ->with(['items.product', 'user', 'payments'])
            ->latest()
            ->paginate(15);

        return view('backoffice.orders', compact('tenant', 'orders'));
    }

    /**
     * AI Business Consultant Chat & Detailed Insights.
     */
    public function aiConsultant(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $todayStr = date('Y-m-d');
        $latestReport = AiDailyReport::where('tenant_id', $tenant->id)
            ->where('report_date', $todayStr)
            ->whereNull('outlet_id')
            ->first();

        if (! $latestReport) {
            $latestReport = $this->consultantService->generateDailyReport($tenant->id, null, $todayStr);
        }

        $metrics = $this->aggregator->aggregateDailyMetrics($tenant->id, null, $todayStr);

        return view('backoffice.ai-consultant', compact('tenant', 'outlet', 'latestReport', 'metrics'));
    }

    /**
     * Trigger Pembuatan Ulang Insight AI secara Live.
     */
    public function triggerAiReport(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $this->consultantService->generateDailyReport($tenant->id, null, date('Y-m-d'));

        return back()->with('status', 'Insight bisnis AI berhasil diperbarui!');
    }
}
