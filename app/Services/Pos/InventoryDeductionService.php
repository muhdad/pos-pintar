<?php

namespace App\Services\Pos;

use App\Models\Order;
use App\Models\OutletStock;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryDeductionService
{
    /**
     * Potong stok bahan baku (BOM) otomatis berdasarkan item pesanan di cabang terkait.
     * Wajib dipanggil di dalam DB::transaction().
     *
     * @param Order $order
     * @return array Rekap alert bahan baku yang stoknya di bawah batas aman
     */
    public function deductForOrder(Order $order): array
    {
        $lowStockAlerts = [];

        // Pastikan relasi items dan recipes termuat
        $order->loadMissing(['items.product.recipes.rawMaterial']);

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->track_stock) {
                continue;
            }

            // Loop setiap komposisi bahan baku (BOM)
            foreach ($product->recipes as $recipe) {
                $rawMaterial = $recipe->rawMaterial;
                if (! $rawMaterial) {
                    continue;
                }

                $quantityRequired = bcmul((string) $item->quantity, (string) $recipe->quantity, 4);

                // Ambil atau inisialisasi baris stok cabang dengan pessimistic lock
                $outletStock = OutletStock::where('outlet_id', $order->outlet_id)
                    ->where('raw_material_id', $rawMaterial->id)
                    ->lockForUpdate()
                    ->first();

                if (! $outletStock) {
                    $outletStock = OutletStock::create([
                        'outlet_id' => $order->outlet_id,
                        'raw_material_id' => $rawMaterial->id,
                        'current_stock' => 0,
                    ]);
                }

                $currentStock = (string) $outletStock->current_stock;
                $balanceAfter = bcsub($currentStock, $quantityRequired, 4);

                // Update saldo stok saat ini
                $outletStock->update([
                    'current_stock' => $balanceAfter,
                ]);

                // Catat mutasi kartu stok (StockMovement)
                StockMovement::create([
                    'outlet_stock_id' => $outletStock->id,
                    'type' => 'SALE',
                    'quantity_delta' => -$quantityRequired,
                    'balance_after' => $balanceAfter,
                    'reference_id' => $order->id,
                    'notes' => "Order #{$order->order_number}: {$item->quantity}x {$product->name}",
                ]);

                // Cek jika stok berada di bawah batas minimum (Critical / Low Stock)
                if (bccomp($balanceAfter, (string) $rawMaterial->minimum_stock, 4) <= 0) {
                    $lowStockAlerts[] = [
                        'raw_material_id' => $rawMaterial->id,
                        'raw_material_name' => $rawMaterial->name,
                        'unit' => $rawMaterial->unit,
                        'remaining_stock' => (float) $balanceAfter,
                        'minimum_stock' => (float) $rawMaterial->minimum_stock,
                    ];

                    Log::warning("POS Low Stock Alert [Outlet: {$order->outlet_id}]: {$rawMaterial->name} sisa {$balanceAfter} {$rawMaterial->unit} (Batas: {$rawMaterial->minimum_stock})");
                }
            }
        }

        return $lowStockAlerts;
    }

    /**
     * Catat penambahan stok bahan baku dari supplier (manual atau via AI OCR invoice).
     * Juga memperbarui HPP rata-rata (Moving Average Cost) secara otomatis.
     */
    public function recordRestock(
        string $outletId,
        string $rawMaterialId,
        float $quantity,
        ?float $costPerUnit = null,
        string $type = 'RESTOCK_MANUAL',
        ?string $referenceId = null,
        ?string $notes = null
    ): OutletStock {
        return DB::transaction(function () use ($outletId, $rawMaterialId, $quantity, $costPerUnit, $type, $referenceId, $notes) {
            $rawMaterial = RawMaterial::findOrFail($rawMaterialId);

            $outletStock = OutletStock::where('outlet_id', $outletId)
                ->where('raw_material_id', $rawMaterialId)
                ->lockForUpdate()
                ->first();

            if (! $outletStock) {
                $outletStock = OutletStock::create([
                    'outlet_id' => $outletId,
                    'raw_material_id' => $rawMaterialId,
                    'current_stock' => 0,
                ]);
            }

            $currentStock = (float) $outletStock->current_stock;
            $newStock = $currentStock + $quantity;

            // Hitung ulang Moving Average HPP jika harga beli disertakan
            if ($costPerUnit !== null && $costPerUnit > 0) {
                $oldCost = (float) $rawMaterial->average_cost;
                $totalExistingValue = max(0, $currentStock) * $oldCost;
                $incomingValue = $quantity * $costPerUnit;
                $totalNewStock = max(0, $currentStock) + $quantity;

                if ($totalNewStock > 0) {
                    $newAverageCost = ($totalExistingValue + $incomingValue) / $totalNewStock;
                    $rawMaterial->update([
                        'average_cost' => round($newAverageCost, 2),
                    ]);
                }
            }

            $outletStock->update([
                'current_stock' => $newStock,
            ]);

            StockMovement::create([
                'outlet_stock_id' => $outletStock->id,
                'type' => $type,
                'quantity_delta' => $quantity,
                'balance_after' => $newStock,
                'reference_id' => $referenceId,
                'notes' => $notes ?? 'Restock pengadaan bahan baku',
            ]);

            return $outletStock;
        });
    }

    /**
     * Catat bahan baku rusak, basi, atau tumpah (Waste / Spoilage).
     */
    public function recordWaste(
        string $outletId,
        string $rawMaterialId,
        float $quantity,
        ?string $notes = null
    ): OutletStock {
        return DB::transaction(function () use ($outletId, $rawMaterialId, $quantity, $notes) {
            $outletStock = OutletStock::where('outlet_id', $outletId)
                ->where('raw_material_id', $rawMaterialId)
                ->lockForUpdate()
                ->firstOrFail();

            $balanceAfter = (float) $outletStock->current_stock - $quantity;

            $outletStock->update([
                'current_stock' => $balanceAfter,
            ]);

            StockMovement::create([
                'outlet_stock_id' => $outletStock->id,
                'type' => 'WASTE',
                'quantity_delta' => -$quantity,
                'balance_after' => $balanceAfter,
                'notes' => $notes ?? 'Barang terbuang / basi / rusak',
            ]);

            return $outletStock;
        });
    }

    /**
     * Catat penyesuaian stok fisik (Stock Opname).
     */
    public function recordOpnameAdjustment(
        string $outletId,
        string $rawMaterialId,
        float $actualStock,
        ?string $notes = null
    ): OutletStock {
        return DB::transaction(function () use ($outletId, $rawMaterialId, $actualStock, $notes) {
            $outletStock = OutletStock::firstOrCreate(
                ['outlet_id' => $outletId, 'raw_material_id' => $rawMaterialId],
                ['current_stock' => 0]
            );

            $currentStock = (float) $outletStock->current_stock;
            $delta = $actualStock - $currentStock;

            $outletStock->update([
                'current_stock' => $actualStock,
            ]);

            StockMovement::create([
                'outlet_stock_id' => $outletStock->id,
                'type' => 'OPNAME_ADJUSTMENT',
                'quantity_delta' => $delta,
                'balance_after' => $actualStock,
                'notes' => $notes ?? 'Penyesuaian stok fisik opname',
            ]);

            return $outletStock;
        });
    }
}

