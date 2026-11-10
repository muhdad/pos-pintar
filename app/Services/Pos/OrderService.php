<?php

namespace App\Services\Pos;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Ai\AiConsultantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected InventoryDeductionService $inventoryDeductionService
    ) {}

    /**
     * Proses transaksi checkout pesanan baru atau sinkronisasi order offline.
     * Menggunakan DB::transaction() untuk menjaga integritas data keuangan & stok.
     *
     * @param array $payload
     * @return Order
     */
    public function checkout(array $payload): Order
    {
        $orderId = $payload['id'] ?? (string) Str::uuid();

        // 1. Cek Idempotensi: Jika order sudah pernah disinkronkan, jangan buat ulang
        $existingOrder = Order::find($orderId);
        if ($existingOrder) {
            return $existingOrder;
        }

        return DB::transaction(function () use ($payload, $orderId) {
            $itemsData = $payload['items'] ?? [];
            $totalAmount = 0;
            $itemsToCreate = [];

            // 2. Siapkan item dan snapshot harga jual serta HPP (unit_cost)
            foreach ($itemsData as $item) {
                $product = Product::with('recipes.rawMaterial')->findOrFail($item['product_id']);

                $quantity = (int) ($item['quantity'] ?? 1);
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $product->selling_price;
                $unitCost = (float) $product->cogs; // Snapshot HPP (AGENTS.md Rule 3)
                $subtotal = $unitPrice * $quantity;
                $totalAmount += $subtotal;

                $itemsToCreate[] = [
                    'id' => (string) Str::uuid(),
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                    'product' => $product,
                ];
            }

            $discountAmount = (float) ($payload['discount_amount'] ?? 0);
            $taxAmount = (float) ($payload['tax_amount'] ?? 0);
            $finalAmount = max(0, $totalAmount - $discountAmount + $taxAmount);

            // Buat nomor struk unik format: POS-YYYYMMDD-XXXXX
            $orderNumber = $payload['order_number'] ?? sprintf(
                'POS-%s-%s',
                date('Ymd'),
                strtoupper(Str::random(5))
            );

            // Resolusi tenant_id, outlet_id, dan user_id yang valid
            $tenantId = ! empty($payload['tenant_id']) && ! in_array($payload['tenant_id'], ['default-tenant', 'demo-tenant'])
                ? $payload['tenant_id']
                : (Tenant::first()->id ?? null);

            $outletId = ! empty($payload['outlet_id']) && ! in_array($payload['outlet_id'], ['default-outlet', 'demo-outlet'])
                ? $payload['outlet_id']
                : (Outlet::where('tenant_id', $tenantId)->first()->id ?? Outlet::first()->id ?? null);

            $userId = ! empty($payload['user_id']) && User::where('id', $payload['user_id'])->exists()
                ? $payload['user_id']
                : (User::first()->id ?? null);

            $shiftId = ! empty($payload['shift_id']) && Shift::where('id', $payload['shift_id'])->exists()
                ? $payload['shift_id']
                : null;

            // 3. Simpan entitas Order
            $order = Order::create([
                'id' => $orderId,
                'tenant_id' => $tenantId,
                'outlet_id' => $outletId,
                'shift_id' => $shiftId,
                'user_id' => $userId,
                'table_id' => ! empty($payload['table_id']) ? $payload['table_id'] : null,
                'order_number' => $orderNumber,
                'status' => strtoupper($payload['status'] ?? 'PAID'),
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'final_amount' => $finalAmount,
                'customer_name' => $payload['customer_name'] ?? null,
                'customer_phone' => $payload['customer_phone'] ?? null,
                'synced_at' => ! empty($payload['is_offline_sync']) ? now() : null,
            ]);

            // 4. Simpan OrderItem dengan snapshot finansial
            foreach ($itemsToCreate as $itemData) {
                OrderItem::create([
                    'id' => $itemData['id'],
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_cost' => $itemData['unit_cost'],
                    'subtotal' => $itemData['subtotal'],
                    'notes' => $itemData['notes'],
                ]);
            }

            // 5. Simpan Payment jika ada
            if (! empty($payload['payment'])) {
                Payment::create([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'method' => strtoupper($payload['payment']['method'] ?? 'CASH'),
                    'amount' => $payload['payment']['amount'] ?? $finalAmount,
                    'status' => strtoupper($payload['payment']['status'] ?? 'SETTLED'),
                    'reference_no' => $payload['payment']['reference_no'] ?? null,
                    'paid_at' => $payload['payment']['paid_at'] ?? now(),
                ]);
            }

            // 6. Pemotongan stok bahan baku resep (BOM) otomatis
            $this->inventoryDeductionService->deductForOrder($order);

            // 7. Catat Audit Log
            AuditLog::create([
                'tenant_id' => $order->tenant_id,
                'user_id' => $order->user_id,
                'action' => 'ORDER_CREATED',
                'description' => "Order {$order->order_number} berhasil dibuat (Total: Rp" . number_format($order->final_amount, 0, ',', '.') . ")",
                'metadata' => [
                    'order_id' => $order->id,
                    'items_count' => count($itemsToCreate),
                    'payment_method' => $payload['payment']['method'] ?? 'CASH',
                    'is_offline_sync' => ! empty($payload['is_offline_sync']),
                ],
            ]);

            // 8. Segera perbarui AI Daily Report hari ini agar omzet langsung tersinkron
            try {
                app(AiConsultantService::class)->generateDailyReport($order->tenant_id, null, date('Y-m-d'));
            } catch (\Throwable $e) {
                Log::warning('Auto-refresh AI report failed after checkout: ' . $e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Batch sync transaksi offline dari browser client Dexie.js ke cloud backend.
     *
     * @param array $ordersList
     * @return array
     */
    public function syncBatch(array $ordersList): array
    {
        $syncedIds = [];
        $errors = [];

        foreach ($ordersList as $orderPayload) {
            try {
                $orderPayload['is_offline_sync'] = true;
                $order = $this->checkout($orderPayload);
                $syncedIds[] = $order->id;
            } catch (\Throwable $e) {
                Log::error("POS Sync Order Error: " . $e->getMessage(), ['order' => $orderPayload]);
                $errors[] = [
                    'order_id' => $orderPayload['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'synced_count' => count($syncedIds),
            'synced_ids' => $syncedIds,
            'errors' => $errors,
        ];
    }

    /**
     * Pembatalan (Void) pesanan dengan otorisasi PIN supervisor/owner.
     */
    public function voidOrder(string $orderId, int $authorizingUserId, string $reason): Order
    {
        return DB::transaction(function () use ($orderId, $authorizingUserId, $reason) {
            $order = Order::with('items.product.recipes.rawMaterial')->findOrFail($orderId);

            if ($order->status === 'VOIDED') {
                return $order;
            }

            $order->update([
                'status' => 'VOIDED',
            ]);

            // Catat log pembatalan transaksi dengan user yang mengotorisasi
            AuditLog::create([
                'tenant_id' => $order->tenant_id,
                'user_id' => $authorizingUserId,
                'action' => 'VOID_ORDER',
                'description' => "Order #{$order->order_number} dibatalkan. Alasan: {$reason}",
                'metadata' => [
                    'order_id' => $order->id,
                    'void_by' => $authorizingUserId,
                    'amount_voided' => (float) $order->final_amount,
                ],
            ]);

            // Refresh AI Report
            try {
                app(AiConsultantService::class)->generateDailyReport($order->tenant_id, null, date('Y-m-d'));
            } catch (\Throwable $e) {
                // ignore
            }

            return $order;
        });
    }
}
