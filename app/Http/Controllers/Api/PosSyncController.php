<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\User;
use App\Services\Pos\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PosSyncController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Endpoint untuk menyinkronkan transaksi offline dari Dexie.js ke PostgreSQL / SQLite Cloud.
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|string',
            'orders.*.tenant_id' => 'required|string',
            'orders.*.outlet_id' => 'required|string',
            'orders.*.items' => 'required|array',
            'orders.*.items.*.product_id' => 'required|string',
            'orders.*.items.*.quantity' => 'required|numeric|min:1',
            'orders.*.payment' => 'nullable|array',
        ]);

        $result = $this->orderService->syncBatch($validated['orders']);

        return response()->json($result);
    }

    /**
     * Ambil data inisialisasi master produk, kategori, dan stok lokal untuk Dexie.js (IndexedDB).
     */
    public function bootstrapData(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id ?? $request->query('tenant_id') ?? \App\Models\Tenant::first()?->id;
        $outletId = $request->query('outlet_id') ?? \App\Models\Outlet::where('tenant_id', $tenantId)->first()?->id;

        if (! $tenantId) {
            return response()->json(['error' => 'tenant_id is required'], 400);
        }

        $categories = Category::where('tenant_id', $tenantId)->get(['id', 'name', 'icon']);

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['recipes.rawMaterial'])
            ->get(['id', 'category_id', 'sku', 'barcode', 'name', 'selling_price', 'cogs', 'image_url', 'track_stock']);

        $stocks = [];
        if ($outletId) {
            $stocks = OutletStock::where('outlet_id', $outletId)
                ->with('rawMaterial:id,name,unit')
                ->get()
                ->map(fn ($stock) => [
                    'raw_material_id' => $stock->raw_material_id,
                    'name' => $stock->rawMaterial?->name,
                    'unit' => $stock->rawMaterial?->unit,
                    'current_stock' => (float) $stock->current_stock,
                ]);
        }

        return response()->json([
            'tenant_id' => $tenantId,
            'outlet_id' => $outletId,
            'categories' => $categories,
            'products' => $products,
            'stocks' => $stocks,
        ]);
    }

    /**
     * Verifikasi 4-digit PIN staf kasir atau otorisasi supervisor.
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:4',
            'role_required' => 'nullable|string|in:SUPERVISOR,OWNER',
        ]);

        $user = $request->user();

        // Cari staf dalam tenant yang sama dengan PIN yang cocok
        $staffMembers = User::where('tenant_id', $user?->tenant_id)
            ->where('is_active', true)
            ->get();

        $matchedUser = null;
        foreach ($staffMembers as $staff) {
            if ($staff->pin_hash && Hash::check($validated['pin'], $staff->pin_hash)) {
                $matchedUser = $staff;
                break;
            }
        }

        if (! $matchedUser) {
            return response()->json([
                'success' => false,
                'message' => 'PIN salah atau staf tidak ditemukan.',
            ], 401);
        }

        // Cek jika butuh otorisasi peran tinggi (Supervisor / Owner)
        if (! empty($validated['role_required'])) {
            if (! in_array($matchedUser->role, ['OWNER', 'MANAGER'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aksi ini memerlukan otorisasi Supervisor atau Owner.',
                ], 403);
            }
        }

        // Simpan sesi PIN
        session([
            'pos_pin_verified' => true,
            'pos_active_cashier_id' => $matchedUser->id,
            'pos_active_cashier_name' => $matchedUser->name,
            'pos_active_cashier_role' => $matchedUser->role,
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $matchedUser->id,
                'name' => $matchedUser->name,
                'role' => $matchedUser->role,
            ],
        ]);
    }
}

