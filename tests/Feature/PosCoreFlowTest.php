<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Ai\BusinessDataAggregator;
use App\Services\Pos\InventoryDeductionService;
use App\Services\Pos\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCoreFlowTest extends TestCase
{
    public function test_complete_pos_order_inventory_deduction_and_cogs_snapshot(): void
    {
        $tenant = Tenant::where('slug', 'kopi-senja')->first();
        $outlet = Outlet::where('tenant_id', $tenant->id)->first();
        $user = User::where('email', 'kasir@kopisenja.com')->first();
        $product = Product::where('name', 'Kopi Susu Gula Aren')->first();

        $bijiKopi = RawMaterial::where('name', 'Biji Kopi House Blend')->first();
        $initialStock = OutletStock::where('outlet_id', $outlet->id)
            ->where('raw_material_id', $bijiKopi->id)
            ->first();

        $initialStockQty = (float) $initialStock->current_stock;

        $orderService = app(OrderService::class);

        // Kasir melakukan order 2x Kopi Susu Gula Aren
        $order = $orderService->checkout([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 22000,
                ],
            ],
            'payment' => [
                'method' => 'CASH',
                'amount' => 44000,
            ],
        ]);

        $this->assertNotNull($order);
        $this->assertEquals(44000, $order->total_amount);

        // 1. Verifikasi Snapshot Finansial di order_items (AGENTS.md Rule 3)
        $orderItem = $order->items()->first();
        $this->assertEquals(22000, (float) $orderItem->unit_price);
        $this->assertEquals(9710, (float) $orderItem->unit_cost); // HPP Snapshot!

        // 2. Verifikasi Pemotongan Stok Bahan Baku Resep BOM (2x 18g = 36g)
        $updatedStock = OutletStock::where('outlet_id', $outlet->id)
            ->where('raw_material_id', $bijiKopi->id)
            ->first();

        $this->assertEquals($initialStockQty - 36, (float) $updatedStock->current_stock);

        // 3. Verifikasi Mutasi Kartu Stok
        $this->assertDatabaseHas('stock_movements', [
            'outlet_stock_id' => $updatedStock->id,
            'type' => 'SALE',
            'quantity_delta' => -36,
        ]);

        // 4. Verifikasi BusinessDataAggregator (AGENTS.md Rule 4)
        $aggregator = app(BusinessDataAggregator.php);
        $metrics = $aggregator->aggregateDailyMetrics($tenant->id, $outlet->id);

        $this->assertGreaterThanOrEqual(44000, $metrics['financial']['total_revenue']);
        $this->assertGreaterThanOrEqual(19420, $metrics['financial']['total_cogs']); // 2 * 9710
        $this->assertEquals('Kopi Susu Gula Aren', $metrics['top_selling'][0]['name']);
    }
}

