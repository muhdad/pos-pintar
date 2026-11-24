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
    use RefreshDatabase;

    public function test_complete_pos_order_inventory_deduction_and_cogs_snapshot(): void
    {
        $this->seed();

        $tenant = Tenant::first();
        $outlet = Outlet::where('tenant_id', $tenant->id)->first();
        $user = User::where('tenant_id', $tenant->id)->first();
        $product = Product::where('name', 'Soto Daging')->first();

        $dagingSapi = RawMaterial::where('name', 'Daging Sapi Pilihan')->first();
        $initialStock = OutletStock::where('outlet_id', $outlet->id)
            ->where('raw_material_id', $dagingSapi->id)
            ->first();

        $initialStockQty = (float) $initialStock->current_stock;

        $orderService = app(OrderService::class);

        // Kasir melakukan order 2x Soto Daging
        $order = $orderService->checkout([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 23000,
                ],
            ],
            'payment' => [
                'method' => 'CASH',
                'amount' => 46000,
            ],
        ]);

        $this->assertNotNull($order);
        $this->assertEquals(46000, $order->total_amount);

        // 1. Verifikasi Snapshot Finansial di order_items (AGENTS.md Rule 3)
        $orderItem = $order->items()->first();
        $this->assertEquals(23000, (float) $orderItem->unit_price);
        $this->assertEquals(12500, (float) $orderItem->unit_cost); // HPP Snapshot!

        // 2. Verifikasi Pemotongan Stok Bahan Baku Resep BOM (2x 90g = 180g)
        $updatedStock = OutletStock::where('outlet_id', $outlet->id)
            ->where('raw_material_id', $dagingSapi->id)
            ->first();

        $this->assertEquals($initialStockQty - 180, (float) $updatedStock->current_stock);

        // 3. Verifikasi Mutasi Kartu Stok
        $this->assertDatabaseHas('stock_movements', [
            'outlet_stock_id' => $updatedStock->id,
            'type' => 'SALE',
            'quantity_delta' => -180,
        ]);

        // 4. Verifikasi BusinessDataAggregator (AGENTS.md Rule 4)
        $aggregator = app(BusinessDataAggregator::class);
        $metrics = $aggregator->aggregateDailyMetrics($tenant->id, $outlet->id);

        $this->assertGreaterThanOrEqual(46000, $metrics['financial']['total_revenue']);
        $this->assertGreaterThanOrEqual(25000, $metrics['financial']['total_cogs']); // 2 * 12500
        $this->assertEquals('Soto Daging', $metrics['top_selling'][0]['name']);
    }
}

