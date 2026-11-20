<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Table;
use App\Models\User;
use App\Services\Pos\OrderService;
use App\Services\Pos\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PosSyncController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ShiftService $shiftService
    ) {}

    /**
     * Endpoint untuk menyinkronkan transaksi offline dari Dexie.js ke cloud backend.
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
            'orders.*.payments' => 'nullable|array',
        ]);

        $result = $this->orderService->syncBatch($validated['orders']);

        return response()->json($result);
    }

    /**
     * Ambil data master untuk Dexie.js (IndexedDB).
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
        $tables = [];
        $activeShift = null;

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

            $tables = Table::where('outlet_id', $outletId)->orderBy('name')->get(['id', 'name', 'is_occupied', 'current_order_id']);

            $activeShift = Shift::where('outlet_id', $outletId)->whereNull('end_time')->latest('start_time')->first();
        }

        return response()->json([
            'tenant_id' => $tenantId,
            'outlet_id' => $outletId,
            'categories' => $categories,
            'products' => $products,
            'stocks' => $stocks,
            'tables' => $tables,
            'active_shift' => $activeShift,
        ]);
    }

    /**
     * Simpan Open Bill (Hold Order) pelanggan yang makan dulu.
     */
    public function holdOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|string',
            'tenant_id' => 'nullable|string',
            'outlet_id' => 'nullable|string',
            'table_id' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'items' => 'required|array',
            'discount_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
        ]);

        $order = $this->orderService->holdOrder($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil disimpan sebagai Open Bill.',
            'order' => $order->load(['items.product', 'table']),
        ]);
    }

    /**
     * Ambil seluruh Open Bill aktif yang belum dibayar di outlet kasir.
     */
    public function openBills(Request $request): JsonResponse
    {
        $outletId = $request->query('outlet_id') ?? Outlet::first()?->id;
        $bills = $this->orderService->getActiveOpenBills($outletId);

        return response()->json([
            'success' => true,
            'data' => $bills,
        ]);
    }

    /**
     * Pelunasan Open Bill.
     */
    public function settleOpenBill(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'payment' => 'nullable|array',
            'payments' => 'nullable|array',
        ]);

        $order = $this->orderService->settleOpenBill($orderId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Open Bill berhasil dilunasi.',
            'order' => $order->load(['items.product', 'table', 'payments']),
        ]);
    }

    /**
     * Pembatalan / Void Transaksi dengan Otorisasi Supervisor.
     */
    public function voidOrder(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:4',
            'reason' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $authorizer = null;

        // Validasi PIN Supervisor / Owner
        $staffMembers = User::where('tenant_id', $user?->tenant_id ?? Tenant::first()?->id)
            ->where('is_active', true)
            ->get();

        foreach ($staffMembers as $staff) {
            if ($staff->pin_hash && Hash::check($validated['pin'], $staff->pin_hash)) {
                if (in_array($staff->role, ['SUPERADMIN', 'OWNER', 'MANAGER'])) {
                    $authorizer = $staff;
                    break;
                }
            }
        }

        if (! $authorizer) {
            return response()->json([
                'success' => false,
                'message' => 'Otorisasi gagal. PIN salah atau pengguna bukan Supervisor/Owner.',
            ], 403);
        }

        $order = $this->orderService->voidOrder($orderId, $authorizer->id, $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => "Order #{$order->order_number} berhasil dibatalkan dan stok telah dikembalikan.",
            'order' => $order,
        ]);
    }

    /**
     * Buka Shift Kasir Baru (Input Modal Awal Laci).
     */
    public function startShift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outlet_id' => 'required|string',
            'terminal_id' => 'nullable|string',
            'start_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $userId = $request->user()?->id ?? User::first()?->id;
        $terminalId = $validated['terminal_id'] ?? \App\Models\Terminal::where('outlet_id', $validated['outlet_id'])->first()?->id ?? (string) \Illuminate\Support\Str::uuid();

        $shift = $this->shiftService->startShift(
            $validated['outlet_id'],
            $terminalId,
            $userId,
            (float) $validated['start_cash'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Shift kasir berhasil dibuka.',
            'shift' => $shift,
        ]);
    }

    /**
     * Catat Pengeluaran Kas Kecil (Petty Cash Out).
     */
    public function recordPettyCash(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shift_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $userId = $request->user()?->id ?? User::first()?->id;

        $log = $this->shiftService->recordPettyCash(
            $validated['shift_id'],
            (float) $validated['amount'],
            $validated['reason'],
            $userId
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran kas kecil berhasil dicatat.',
            'log' => $log,
        ]);
    }

    /**
     * Tutup Shift Kasir (Blind Close).
     */
    public function closeShift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shift_id' => 'required|string',
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift = $this->shiftService->closeShift(
            $validated['shift_id'],
            (float) $validated['actual_cash'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Shift kasir berhasil ditutup.',
            'shift' => $shift,
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
        $staffMembers = User::where('tenant_id', $user?->tenant_id ?? Tenant::first()?->id)
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

        if (! empty($validated['role_required'])) {
            if (! in_array($matchedUser->role, ['SUPERADMIN', 'OWNER', 'MANAGER'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aksi ini memerlukan otorisasi Supervisor atau Owner.',
                ], 403);
            }
        }

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
