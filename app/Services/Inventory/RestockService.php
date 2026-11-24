<?php

namespace App\Services\Inventory;

use App\Models\Cashflow;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RestockService
{
    /**
     * Konversi kuantitas dari satuan beli (fromUnit) ke satuan sistem bahan baku (toUnit).
     */
    public function convertQuantity(float $inputQty, string $fromUnit, string $toUnit, ?float $customFactor = null): float
    {
        $from = strtolower(trim($fromUnit));
        $to = strtolower(trim($toUnit));

        // Normalisasi alias
        $from = $this->normalizeUnit($from);
        $to = $this->normalizeUnit($to);

        // Jika satuan sama, tidak perlu konversi
        if ($from === $to) {
            return $inputQty;
        }

        // Jika user memberikan faktor kustom eksplisit (misal 1 dus = 24 botol / 1 tray = 30 butir)
        if ($customFactor !== null && $customFactor > 0) {
            return $inputQty * $customFactor;
        }

        // --- Konversi Massa / Berat ---
        // Basis standar massa: gram
        $massToGram = [
            'gram' => 1.0,
            'ons' => 100.0,
            'kg' => 1000.0,
            'kuintal' => 100000.0,
            'ton' => 1000000.0,
        ];

        if (isset($massToGram[$from]) && isset($massToGram[$to])) {
            $inGrams = $inputQty * $massToGram[$from];
            return $inGrams / $massToGram[$to];
        }

        // --- Konversi Volume / Cairan ---
        // Basis standar volume: ml
        $volumeToMl = [
            'ml' => 1.0,
            'liter' => 1000.0,
            'galon' => 19000.0,
        ];

        if (isset($volumeToMl[$from]) && isset($volumeToMl[$to])) {
            $inMl = $inputQty * $volumeToMl[$from];
            return $inMl / $volumeToMl[$to];
        }

        // --- Konversi Kuantitas / Kemasan Lusin / Kodi / Tray ---
        // Basis standar kuantitas: pcs
        $countToPcs = [
            'pcs' => 1.0,
            'butir' => 1.0,
            'biji' => 1.0,
            'buah' => 1.0,
            'lusin' => 12.0,
            'kodi' => 20.0,
            'gross' => 144.0,
            'tray' => 30.0, // Rak telur standar 30 butir
        ];

        if (isset($countToPcs[$from]) && isset($countToPcs[$to])) {
            $inPcs = $inputQty * $countToPcs[$from];
            return $inPcs / $countToPcs[$to];
        }

        // Jika tidak ada aturan otomatis dan tidak ada custom factor, fallback 1:1
        return $inputQty;
    }

    /**
     * Normalisasi nama satuan agar tahan terhadap variasi penulisan.
     */
    public function normalizeUnit(string $unit): string
    {
        $u = strtolower(trim($unit));

        return match ($u) {
            'gr', 'g', 'gram' => 'gram',
            'kg', 'kilo', 'kilogram' => 'kg',
            'ons', 'hg' => 'ons',
            'kwintal', 'kuintal' => 'kuintal',
            'ton' => 'ton',
            'ml', 'mili', 'mililiter' => 'ml',
            'l', 'lt', 'liter' => 'liter',
            'galon' => 'galon',
            'pcs', 'pc', 'buah', 'biji', 'potong', 'porsi', 'ekor', 'lembar' => 'pcs',
            'butir' => 'butir',
            'lusin', 'dozen' => 'lusin',
            'kodi' => 'kodi',
            'tray', 'rak' => 'tray',
            default => $u,
        };
    }

    /**
     * Hitung Moving Average HPP (Harga Pokok Pembelian per Satuan Sistem).
     * Rumus: ((Stok Lama * HPP Lama) + Total Harga Beli Baru) / (Stok Lama + Kuantitas Tambahan)
     */
    public function calculateNewHpp(float $currentStock, float $currentHpp, float $addedConvertedQty, float $totalPurchasePrice): float
    {
        $safeCurrentStock = max(0, $currentStock);
        $totalNewStock = $safeCurrentStock + $addedConvertedQty;

        if ($totalNewStock <= 0) {
            return $addedConvertedQty > 0 ? round($totalPurchasePrice / $addedQty, 2) : $currentHpp;
        }

        $totalValuation = ($safeCurrentStock * $currentHpp) + $totalPurchasePrice;

        return round($totalValuation / $totalNewStock, 2);
    }

    /**
     * Eksekusi Restock Bahan Baku ke Database:
     * 1. Konversi satuan beli ke satuan sistem.
     * 2. Hitung HPP Moving Average.
     * 3. Tambah OutletStock.
     * 4. Catat riwayat StockMovement.
     * 5. Catat pengeluaran ke Arus Kas (Cashflow OUTFLOW) untuk memotong omset kas.
     */
    public function executeRestock(array $data, string $outletId, string $tenantId, ?string $userId = null): array
    {
        $material = RawMaterial::where('tenant_id', $tenantId)->findOrFail($data['raw_material_id']);
        $systemUnit = $material->unit ?? 'gram';

        $purchaseQty = (float) $data['purchase_quantity'];
        $purchaseUnit = (string) ($data['purchase_unit'] ?? $systemUnit);
        $customFactor = !empty($data['custom_conversion_factor']) ? (float) $data['custom_conversion_factor'] : null;

        // 1. Konversi otomatis
        $convertedQty = $this->convertQuantity($purchaseQty, $purchaseUnit, $systemUnit, $customFactor);

        // 2. Harga Total Belanja
        if (isset($data['total_price']) && (float) $data['total_price'] > 0) {
            $totalPrice = (float) $data['total_price'];
        } elseif (isset($data['price_per_purchase_unit'])) {
            $totalPrice = $purchaseQty * (float) $data['price_per_purchase_unit'];
        } else {
            $totalPrice = 0.0;
        }

        $notes = $data['notes'] ?? null;
        $supplier = $data['supplier_name'] ?? 'Pasar / Supplier';
        $sourceAccount = $data['source_account'] ?? 'CASH_DRAWER';
        $transactionDate = $data['transaction_date'] ?? Carbon::today()->toDateString();
        $recordCashflow = filter_var($data['record_cashflow'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $result = [];

        DB::transaction(function () use (
            $material,
            $systemUnit,
            $outletId,
            $tenantId,
            $userId,
            $purchaseQty,
            $purchaseUnit,
            $convertedQty,
            $totalPrice,
            $notes,
            $supplier,
            $sourceAccount,
            $transactionDate,
            $recordCashflow,
            &$result
        ) {
            $stock = OutletStock::firstOrCreate(
                ['outlet_id' => $outletId, 'raw_material_id' => $material->id],
                ['current_stock' => 0]
            );

            $oldStock = (float) $stock->current_stock;
            $oldHpp = (float) $material->average_cost;

            // 3. Kalkulasi HPP Baru
            $newHpp = $this->calculateNewHpp($oldStock, $oldHpp, $convertedQty, $totalPrice);

            // Update stok dan HPP bahan baku
            $stock->increment('current_stock', $convertedQty);
            $material->update(['average_cost' => $newHpp]);

            // 4. Catat StockMovement
            $notesString = "Restock: Beli {$purchaseQty} {$purchaseUnit} (Otomatis konversi jadi +{$convertedQty} {$systemUnit}) dari {$supplier}";
            if ($notes) {
                $notesString .= " - Catatan: {$notes}";
            }

            $movement = StockMovement::create([
                'outlet_stock_id' => $stock->id,
                'type' => 'IN',
                'quantity_delta' => $convertedQty,
                'balance_after' => $stock->current_stock,
                'notes' => $notesString,
                'created_at' => Carbon::parse($transactionDate)->setTimeFrom(now()),
            ]);

            // 5. Catat ke Cashflow OUTFLOW (Pengeluaran Belanja Bahan Pengurang Omset)
            $cashflow = null;
            if ($recordCashflow && $totalPrice > 0) {
                $cashflow = Cashflow::create([
                    'tenant_id' => $tenantId,
                    'outlet_id' => $outletId,
                    'user_id' => $userId,
                    'type' => 'OUTFLOW',
                    'category' => 'RESTOCK_PURCHASE',
                    'amount' => $totalPrice,
                    'source_account' => $sourceAccount,
                    'transaction_date' => $transactionDate,
                    'description' => "Belanja Restock {$material->name}: {$purchaseQty} {$purchaseUnit} (-> {$convertedQty} {$systemUnit}) - {$supplier}",
                    'is_h_minus_one_deduction' => true,
                ]);
            }

            $result = [
                'material_name' => $material->name,
                'system_unit' => $systemUnit,
                'purchase_qty' => $purchaseQty,
                'purchase_unit' => $purchaseUnit,
                'converted_qty' => $convertedQty,
                'old_stock' => $oldStock,
                'new_stock' => $stock->current_stock,
                'old_hpp' => $oldHpp,
                'new_hpp' => $newHpp,
                'total_price' => $totalPrice,
                'stock_movement_id' => $movement->id,
                'cashflow_id' => $cashflow?->id,
            ];
        });

        return $result;
    }
}
