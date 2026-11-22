<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AiDailyReport;
use App\Models\Cashflow;
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
use App\Services\Ai\ReceiptOcrService;
use App\Services\Export\ExcelExportService;
use App\Services\Pos\InventoryDeductionService;
use App\Services\Pos\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackofficeController extends Controller
{
    public function __construct(
        protected BusinessDataAggregator $aggregator,
        protected AiConsultantService $consultantService,
        protected OrderService $orderService,
        protected InventoryDeductionService $inventoryService
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

        // Distribusi penjualan per jam hari ini (Peak Hours 08:00 - 22:00)
        $hourlyDistribution = [];
        for ($h = 8; $h <= 22; $h++) {
            $hourStr = sprintf('%02d:00', $h);
            $hourlyDistribution[$hourStr] = 0;
        }

        $ordersToday = Order::where('tenant_id', $tenant->id)
            ->whereDate('created_at', $todayStr)
            ->where('status', 'PAID')
            ->get(['final_amount', 'created_at']);

        foreach ($ordersToday as $ord) {
            $h = (int) $ord->created_at->format('H');
            if ($h >= 8 && $h <= 22) {
                $hourStr = sprintf('%02d:00', $h);
                $hourlyDistribution[$hourStr] += (float) $ord->final_amount;
            }
        }

        return view('dashboard', compact(
            'tenant',
            'outlet',
            'metrics',
            'latestAiReport',
            'recentOrders',
            'lowStockItems',
            'totalProductsCount',
            'totalRawMaterialsCount',
            'hourlyDistribution'
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

    /**
     * Ekspor Laporan Riwayat Transaksi ke format CSV / Excel.
     */
    public function exportOrdersCsv(Request $request): StreamedResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();

        $query = Order::where('tenant_id', $tenant->id)
            ->with(['items.product', 'user', 'payments', 'table'])
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->get();
        $filename = "laporan-penjualan-pos-" . date('Ymd-His') . ".csv";

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            // Tulis BOM UTF-8 agar karakter Rupiah dan aksen tampil sempurna di Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header kolom CSV
            fputcsv($handle, [
                'No. Struk',
                'Tanggal & Waktu',
                'Kasir',
                'Meja / Pelanggan',
                'Status Transaksi',
                'Jumlah Item',
                'Daftar Menu & Qty',
                'Subtotal (Rp)',
                'Diskon (Rp)',
                'Pajak (Rp)',
                'Total Akhir (Rp)',
                'Metode Pembayaran',
                'Catatan',
            ]);

            foreach ($orders as $order) {
                $itemsSummary = $order->items->map(function ($it) {
                    $itemText = "{$it->quantity}x " . ($it->product->name ?? 'Menu');
                    if ($it->notes) {
                        $itemText .= " ({$it->notes})";
                    }
                    return $itemText;
                })->join('; ');

                $paymentMethods = $order->payments->pluck('method')->join(', ') ?: 'CASH';

                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->user->name ?? 'Kasir',
                    $order->table ? $order->table->name : ($order->customer_name ?? 'Pelanggan'),
                    $order->status,
                    $order->items->sum('quantity'),
                    $itemsSummary,
                    $order->total_amount,
                    $order->discount_amount,
                    $order->tax_amount,
                    $order->final_amount,
                    $paymentMethods,
                    $order->customer_name ?? '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Pembatalan (Void) transaksi dari Backoffice dengan audit log dan reversal stok.
     */
    public function voidOrderBackoffice(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $this->orderService->voidOrder($order->id, auth()->id(), $validated['reason']);

        return back()->with('status', "Pesanan #{$order->order_number} berhasil dibatalkan (VOID) dan stok bahan telah dikembalikan.");
    }

    /**
     * Catat Penyesuaian Fisik Stok Gudang (Stock Opname).
     */
    public function recordOpname(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_stock_id' => 'required|exists:outlet_stocks,id',
            'physical_stock' => 'required|numeric|min:0',
            'notes' => 'required|string|max:255',
        ]);

        $outletStock = OutletStock::with('rawMaterial')->findOrFail($validated['outlet_stock_id']);

        $this->inventoryService->recordOpnameAdjustment(
            $outletStock->outlet_id,
            $outletStock->raw_material_id,
            (float) $validated['physical_stock'],
            $validated['notes']
        );

        return back()->with('status', "Stock Opname bahan {$outletStock->rawMaterial->name} berhasil diperbarui menjadi {$validated['physical_stock']} {$outletStock->rawMaterial->unit}!");
    }

    /**
     * Bersihkan Seluruh Data Dummy Sistem (Persiapan Input Data Riil).
     */
    public function resetDummyData(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Artisan::call('app:reset-dummy-data');

        return redirect()->route('backoffice.products')->with('status', 'Seluruh data dummy berhasil dibersihkan! Katalog produk dan gudang kini bersih dan siap diisi data riil.');
    }

    /**
     * Export Seluruh Data Katalog Produk, Bahan Baku & Resep (BOM) ke Format JSON.
     */
    public function exportCatalog(Request $request): StreamedResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $categories = Category::where('tenant_id', $tenant->id)->get(['id', 'name', 'icon']);
        
        $rawMaterials = RawMaterial::where('tenant_id', $tenant->id)
            ->with(['outletStocks' => fn ($q) => $q->where('outlet_id', $outlet->id)])
            ->get();

        $products = Product::where('tenant_id', $tenant->id)
            ->with(['recipes.rawMaterial', 'category'])
            ->get();

        $exportData = [
            'app' => 'SLL POS Pintar',
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'categories' => $categories->map(fn ($c) => [
                'name' => $c->name,
                'icon' => $c->icon,
            ]),
            'raw_materials' => $rawMaterials->map(fn ($rm) => [
                'name' => $rm->name,
                'sku' => $rm->sku,
                'unit' => $rm->unit,
                'average_cost' => (float) $rm->average_cost,
                'minimum_stock' => (float) $rm->minimum_stock,
                'current_stock' => (float) ($rm->outletStocks->first()->current_stock ?? 0),
            ]),
            'products' => $products->map(fn ($p) => [
                'category_name' => $p->category?->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'name' => $p->name,
                'description' => $p->description,
                'selling_price' => (float) $p->selling_price,
                'cogs' => (float) $p->cogs,
                'track_stock' => (bool) $p->track_stock,
                'is_active' => (bool) $p->is_active,
                'recipes' => $p->recipes->map(fn ($r) => [
                    'raw_material_name' => $r->rawMaterial?->name,
                    'quantity' => (float) $r->quantity,
                ]),
            ]),
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $fileName = 'katalog_sll_' . date('Y-m-d_His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    /**
     * Import Data Katalog Produk, Bahan Baku & Resep (BOM) dari File JSON.
     */
    public function importCatalog(Request $request): RedirectResponse
    {
        $request->validate([
            'catalog_file' => 'required|file',
        ]);

        $file = $request->file('catalog_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (! is_array($data) || (empty($data['categories']) && empty($data['products']) && empty($data['raw_materials']))) {
            return back()->with('error', 'Format file JSON tidak sesuai atau data di dalamnya kosong.');
        }

        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        DB::transaction(function () use ($tenant, $outlet, $data) {
            // 1. Sync Categories
            $categoryMap = [];
            foreach ($data['categories'] ?? [] as $catData) {
                if (empty($catData['name'])) continue;
                $category = Category::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $catData['name']],
                    ['icon' => $catData['icon'] ?? 'soup']
                );
                $categoryMap[$category->name] = $category->id;
            }

            // 2. Sync Raw Materials & Stok Gudang
            $rawMaterialMap = [];
            foreach ($data['raw_materials'] ?? [] as $rmData) {
                if (empty($rmData['name'])) continue;
                $rawMaterial = RawMaterial::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $rmData['name']],
                    [
                        'sku' => $rmData['sku'] ?? ('RM-' . strtoupper(Str::random(6))),
                        'unit' => $rmData['unit'] ?? 'gram',
                        'average_cost' => $rmData['average_cost'] ?? 0,
                        'minimum_stock' => $rmData['minimum_stock'] ?? 0,
                    ]
                );
                $rawMaterialMap[$rawMaterial->name] = $rawMaterial->id;

                $stockQty = (float) ($rmData['current_stock'] ?? 0);
                $outletStock = OutletStock::updateOrCreate(
                    ['outlet_id' => $outlet->id, 'raw_material_id' => $rawMaterial->id],
                    ['current_stock' => $stockQty]
                );

                if ($stockQty > 0) {
                    StockMovement::firstOrCreate(
                        [
                            'outlet_stock_id' => $outletStock->id,
                            'type' => 'RESTOCK_MANUAL',
                            'notes' => 'Import saldo awal stok bahan baku',
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'quantity_delta' => $stockQty,
                            'balance_after' => $stockQty,
                        ]
                    );
                }
            }

            // 3. Sync Products & Recipes (BOM)
            foreach ($data['products'] ?? [] as $prodData) {
                if (empty($prodData['name'])) continue;

                $categoryId = null;
                if (! empty($prodData['category_name']) && isset($categoryMap[$prodData['category_name']])) {
                    $categoryId = $categoryMap[$prodData['category_name']];
                } elseif (! empty($prodData['category_name'])) {
                    $newCat = Category::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenant->id,
                        'name' => $prodData['category_name'],
                        'icon' => 'soup',
                    ]);
                    $categoryMap[$newCat->name] = $newCat->id;
                    $categoryId = $newCat->id;
                }

                $product = Product::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $prodData['name']],
                    [
                        'category_id' => $categoryId,
                        'sku' => ! empty($prodData['sku']) ? $prodData['sku'] : ('SKU-' . strtoupper(Str::random(6))),
                        'barcode' => $prodData['barcode'] ?? null,
                        'description' => $prodData['description'] ?? null,
                        'selling_price' => $prodData['selling_price'] ?? 0,
                        'cogs' => $prodData['cogs'] ?? 0,
                        'track_stock' => $prodData['track_stock'] ?? true,
                        'is_active' => $prodData['is_active'] ?? true,
                    ]
                );

                // Re-link resep
                if (! empty($prodData['recipes']) && is_array($prodData['recipes'])) {
                    $product->recipes()->delete();
                    foreach ($prodData['recipes'] as $rec) {
                        $matName = $rec['raw_material_name'] ?? null;
                        if ($matName && isset($rawMaterialMap[$matName])) {
                            Recipe::create([
                                'id' => (string) Str::uuid(),
                                'product_id' => $product->id,
                                'raw_material_id' => $rawMaterialMap[$matName],
                                'quantity' => (float) ($rec['quantity'] ?? 1),
                            ]);
                        }
                    }
                }
            }
        });

        $countProducts = count($data['products'] ?? []);
        $countMaterials = count($data['raw_materials'] ?? []);
        $countCategories = count($data['categories'] ?? []);

        return back()->with('status', "Import berhasil! {$countProducts} produk, {$countMaterials} bahan baku, dan {$countCategories} kategori telah sinkron.");
    }

    /**
     * Scan foto struk belanja bahan baku via AI OCR (Gemini Vision / Fallback).
     */
    public function scanReceiptOcr(Request $request, ReceiptOcrService $ocrService): JsonResponse
    {
        $request->validate([
            'receipt_image' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        try {
            $result = $ocrService->scanReceipt(
                $request->file('receipt_image'),
                $outlet?->id,
                $tenant?->id
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindai struk: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Konfirmasi restock bahan baku dari hasil scan struk belanja:
     * Menyesuaikan stok gudang, menghitung HPP baru (Moving Average),
     * dan otomatis mencatat ke pengeluaran Arus Kas (dipotong dari Omzet H-1).
     */
    public function confirmOcrRestock(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $validated = $request->validate([
            'merchant_name' => 'nullable|string|max:150',
            'receipt_image_path' => 'nullable|string',
            'record_cashflow' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.raw_material_id' => 'required|exists:raw_materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        $totalPurchase = 0;
        $restockedNames = [];

        DB::transaction(function () use ($outlet, $tenant, $validated, $request, &$totalPurchase, &$restockedNames) {
            foreach ($validated['items'] as $item) {
                $material = RawMaterial::find($item['raw_material_id']);
                if (! $material) {
                    continue;
                }

                $stock = OutletStock::firstOrCreate(
                    ['outlet_id' => $outlet->id, 'raw_material_id' => $material->id],
                    ['current_stock' => 0]
                );

                $oldStock = max(0, (float) $stock->current_stock);
                $oldHpp = (float) $material->average_cost;
                $addedQty = (float) $item['quantity'];
                $linePrice = (float) $item['total_price'];

                // Moving Average Cost Calculation:
                // New HPP = ((Old Stock * Old HPP) + Purchase Price) / (Old Stock + Added Qty)
                if (($oldStock + $addedQty) > 0) {
                    $newHpp = round((($oldStock * $oldHpp) + $linePrice) / ($oldStock + $addedQty), 2);
                } else {
                    $newHpp = $addedQty > 0 ? round($linePrice / $addedQty, 2) : $oldHpp;
                }

                $material->update(['average_cost' => $newHpp]);
                $stock->increment('current_stock', $addedQty);

                StockMovement::create([
                    'outlet_stock_id' => $stock->id,
                    'type' => 'IN',
                    'quantity_delta' => $addedQty,
                    'balance_after' => $stock->current_stock,
                    'notes' => 'Auto Restock Struk AI: ' . ($validated['merchant_name'] ?? 'Pasar') . " (HPP baru: Rp " . number_format($newHpp, 0, ',', '.') . "/{$material->unit})",
                ]);

                $totalPurchase += $linePrice;
                $restockedNames[] = "{$material->name} (+{$addedQty} {$material->unit})";
            }

            // Catat otomatis ke Arus Kas (Outflow Restock dipotong dari Omzet H-1)
            if ($request->boolean('record_cashflow', true) && $totalPurchase > 0) {
                Cashflow::create([
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                    'user_id' => $request->user()?->id,
                    'type' => 'OUTFLOW',
                    'category' => 'RESTOCK_PURCHASE',
                    'amount' => $totalPurchase,
                    'source_account' => 'CASH_DRAWER',
                    'transaction_date' => now()->toDateString(),
                    'description' => 'Restock Bahan Struk AI: ' . ($validated['merchant_name'] ?? 'Supplier/Pasar') . ' (' . implode(', ', $restockedNames) . ')',
                    'receipt_image_path' => $validated['receipt_image_path'] ?? null,
                    'is_h_minus_one_deduction' => true,
                ]);
            }
        });

        $count = count($validated['items']);
        return back()->with('status', "Auto Restock berhasil! {$count} bahan baku telah ditambahkan ke stok dan HPP otomatis disesuaikan (Total Belanja: Rp " . number_format($totalPurchase, 0, ',', '.') . ").");
    }

    /**
     * Halaman Pengaturan Outlet & Pembayaran QRIS (Khusus Superadmin & Owner).
     */
    public function settings(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        return view('backoffice.settings.index', compact('tenant', 'outlet'));
    }

    /**
     * Simpan Pengaturan Outlet & Pembayaran QRIS Toko.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'qris_payload' => 'nullable|string',
            'qris_merchant_name' => 'nullable|string|max:100',
            'qris_city' => 'nullable|string|max:50',
            'qris_image' => 'nullable|image|max:5120',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'qris_payload' => ! empty($validated['qris_payload']) ? trim($validated['qris_payload']) : null,
            'qris_merchant_name' => $validated['qris_merchant_name'] ?? null,
            'qris_city' => $validated['qris_city'] ?? null,
        ];

        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $filename = 'qris_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/qris');
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $updateData['qris_image_path'] = 'uploads/qris/' . $filename;
        }

        $outlet->update($updateData);

        return back()->with('status', 'Pengaturan outlet dan QRIS toko berhasil disimpan!');
    }

    /**
     * Export Laporan Stok Gudang & Valuasi Bahan Baku ke Format Excel (.xls).
     */
    public function exportInventoryExcel(Request $request, ExcelExportService $excelService): StreamedResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $rawMaterials = RawMaterial::where('tenant_id', $tenant->id)
            ->with(['outletStocks' => fn ($q) => $q->where('outlet_id', $outlet->id)])
            ->get();

        $totalValuation = 0;
        $totalItems = $rawMaterials->count();
        $lowStockCount = 0;

        $headers = [
            'No',
            'Kode SKU',
            'Nama Bahan Baku',
            'Stok Fisik Saat Ini',
            'Satuan Unit',
            'Stok Minimum',
            'HPP Rata-rata per Unit (Rp)',
            'Total Nilai Aset Stok (Rp)',
            'Status Stok Gudang'
        ];

        $rows = [];
        foreach ($rawMaterials as $i => $rm) {
            $currentStock = (float) ($rm->outletStocks->first()->current_stock ?? 0);
            $minStock = (float) $rm->minimum_stock;
            $avgCost = (float) $rm->average_cost;
            $lineValuation = $currentStock * $avgCost;
            $totalValuation += $lineValuation;

            $statusText = 'AMAN';
            $statusType = 'badge-in';
            if ($currentStock <= 0) {
                $statusText = 'HABIS (0)';
                $statusType = 'badge-out';
                $lowStockCount++;
            } elseif ($currentStock <= $minStock) {
                $statusText = 'MENIPIS';
                $statusType = 'badge-warn';
                $lowStockCount++;
            }

            $rows[] = [
                ['value' => $i + 1, 'type' => 'center'],
                ['value' => $rm->sku ?? '-', 'type' => 'text'],
                ['value' => $rm->name, 'type' => 'text'],
                ['value' => $currentStock, 'type' => 'num'],
                ['value' => $rm->unit, 'type' => 'center'],
                ['value' => $minStock, 'type' => 'num'],
                ['value' => $avgCost, 'type' => 'num'],
                ['value' => $lineValuation, 'type' => 'currency'],
                ['value' => $statusText, 'type' => $statusType],
            ];
        }

        $totals = [
            ['value' => 'TOTAL VALUASI', 'type' => 'center'],
            ['value' => '', 'type' => 'text'],
            ['value' => "{$totalItems} Bahan Baku", 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => $totalValuation, 'type' => 'currency'],
            ['value' => "{$lowStockCount} Perlu Restock", 'type' => 'badge-warn'],
        ];

        $summary = [
            'Total Jenis Bahan' => $totalItems . ' Item',
            'Total Valuasi Nilai Stok' => 'Rp ' . number_format($totalValuation, 0, ',', '.'),
            'Bahan Perlu Restock' => $lowStockCount . ' Item',
            'Cabang Gudang' => $outlet->name ?? 'Cabang Utama',
        ];

        $filename = 'Laporan_Stok_Gudang_' . date('Ymd_His') . '.xls';
        $title = 'LAPORAN STOK GUDANG & VALUASI BAHAN BAKU - ' . strtoupper($outlet->name ?? 'SOTO LAMONGAN LESTARI');
        $subtitle = 'Dicetak pada: ' . now()->translatedFormat('d F Y H:i') . ' WIB | Gudang: ' . ($outlet->name ?? 'Cabang Utama');

        return $excelService->download($filename, $title, $subtitle, $summary, $headers, $rows, $totals);
    }

    /**
     * Export Laporan Riwayat Transaksi Penjualan ke Format Excel (.xls).
     */
    public function exportOrdersExcel(Request $request, ExcelExportService $excelService): StreamedResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $query = Order::where('tenant_id', $tenant->id)
            ->with(['items.product', 'user', 'payments', 'table'])
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->get();

        $totalRevenue = (float) $orders->where('status', 'PAID')->sum('final_amount');
        $totalPaidCount = $orders->where('status', 'PAID')->count();
        $totalVoidCount = $orders->where('status', 'VOID')->count();

        $headers = [
            'No',
            'No. Struk',
            'Tanggal & Jam',
            'Kasir',
            'Meja / Pelanggan',
            'Tipe Pesanan',
            'Status',
            'Jumlah Item',
            'Daftar Menu Dipesan',
            'Subtotal (Rp)',
            'Diskon (Rp)',
            'Total Bayar (Rp)',
            'Metode Pembayaran',
            'Catatan'
        ];

        $rows = [];
        foreach ($orders as $i => $order) {
            $itemsSummary = $order->items->map(function ($it) {
                return "{$it->quantity}x " . ($it->product->name ?? 'Menu');
            })->implode(', ');

            $paymentMethod = $order->payments->pluck('payment_method')->unique()->implode(', ');
            if (empty($paymentMethod)) {
                $paymentMethod = $order->payment_method ?? 'TUNAI';
            }

            $isPaid = ($order->status === 'PAID');

            $rows[] = [
                ['value' => $i + 1, 'type' => 'center'],
                ['value' => $order->invoice_number, 'type' => 'text'],
                ['value' => $order->created_at->format('d/m/Y H:i'), 'type' => 'center'],
                ['value' => $order->user?->name ?? 'Kasir', 'type' => 'text'],
                ['value' => $order->table ? $order->table->name : ($order->customer_name ?: '-'), 'type' => 'text'],
                ['value' => $order->order_type ?? 'DINE_IN', 'type' => 'center'],
                ['value' => $order->status, 'type' => $isPaid ? 'badge-in' : 'badge-out'],
                ['value' => (int) $order->items->sum('quantity'), 'type' => 'num'],
                ['value' => $itemsSummary, 'type' => 'text'],
                ['value' => (float) $order->total_amount, 'type' => 'num'],
                ['value' => (float) ($order->discount_amount ?? 0), 'type' => 'num'],
                ['value' => (float) $order->final_amount, 'type' => 'currency'],
                ['value' => $paymentMethod, 'type' => 'center'],
                ['value' => $order->notes ?? '-', 'type' => 'text'],
            ];
        }

        $totals = [
            ['value' => 'TOTAL OMZET', 'type' => 'center'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => "Lunas: {$totalPaidCount} | Batal: {$totalVoidCount}", 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => (float) $totalRevenue, 'type' => 'currency'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
        ];

        $summary = [
            'Total Transaksi' => $orders->count() . ' Pesanan',
            'Transaksi Lunas' => $totalPaidCount . ' Transaksi',
            'Transaksi Batal (Void)' => $totalVoidCount . ' Transaksi',
            'Total Omzet Penjualan' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
        ];

        $filename = 'Laporan_Penjualan_POS_' . date('Ymd_His') . '.xls';
        $title = 'LAPORAN TRANSAKSI PENJUALAN KASIR - ' . strtoupper($outlet->name ?? 'SOTO LAMONGAN LESTARI');
        $subtitle = 'Dicetak pada: ' . now()->translatedFormat('d F Y H:i') . ' WIB';

        return $excelService->download($filename, $title, $subtitle, $summary, $headers, $rows, $totals);
    }

    /**
     * Export Laporan Menu & Resep HPP ke Format Excel (.xls).
     */
    public function exportProductsExcel(Request $request, ExcelExportService $excelService): StreamedResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $products = Product::where('tenant_id', $tenant->id)
            ->with(['category', 'recipes.rawMaterial'])
            ->get();

        $headers = [
            'No',
            'Kategori',
            'SKU / Kode',
            'Nama Menu',
            'Harga Jual (Rp)',
            'HPP Bahan Baku (Rp)',
            'Margin Keuntungan (Rp)',
            'Persentase Margin (%)',
            'Rincian Resep Bahan Baku (BOM)',
            'Status Menu'
        ];

        $rows = [];
        foreach ($products as $i => $p) {
            $price = (float) $p->selling_price;
            $hpp = (float) $p->cogs;
            $marginRp = $price - $hpp;
            $marginPct = $price > 0 ? ($marginRp / $price) : 0;

            $recipeSummary = $p->recipes->map(function ($r) {
                return ($r->rawMaterial->name ?? 'Bahan') . ' ' . $r->quantity . ' ' . ($r->rawMaterial->unit ?? 'unit');
            })->implode(', ');

            if (empty($recipeSummary)) {
                $recipeSummary = 'Tanpa Resep (Langsung Jual)';
            }

            $rows[] = [
                ['value' => $i + 1, 'type' => 'center'],
                ['value' => $p->category?->name ?? 'Umum', 'type' => 'text'],
                ['value' => $p->sku ?? '-', 'type' => 'text'],
                ['value' => $p->name, 'type' => 'text'],
                ['value' => $price, 'type' => 'currency'],
                ['value' => $hpp, 'type' => 'num'],
                ['value' => $marginRp, 'type' => 'currency'],
                ['value' => round($marginPct * 100, 1) . '%', 'type' => 'center'],
                ['value' => $recipeSummary, 'type' => 'text'],
                ['value' => $p->is_active ? 'AKTIF' : 'NONAKTIF', 'type' => $p->is_active ? 'badge-in' : 'badge-out'],
            ];
        }

        $summary = [
            'Total Menu' => $products->count() . ' Menu',
            'Menu Aktif' => $products->where('is_active', true)->count() . ' Menu',
            'Harga Jual Rata-rata' => 'Rp ' . number_format($products->avg('selling_price') ?: 0, 0, ',', '.'),
            'HPP Bahan Rata-rata' => 'Rp ' . number_format($products->avg('cogs') ?: 0, 0, ',', '.'),
        ];

        $filename = 'Laporan_Menu_Resep_HPP_' . date('Ymd_His') . '.xls';
        $title = 'KATALOG MENU & HPP RESEP (BOM) - ' . strtoupper($outlet->name ?? 'SOTO LAMONGAN LESTARI');
        $subtitle = 'Dicetak pada: ' . now()->translatedFormat('d F Y H:i') . ' WIB';

        return $excelService->download($filename, $title, $subtitle, $summary, $headers, $rows);
    }
}
