<?php

namespace App\Services\Ai;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OutletStock;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessDataAggregator
{
    /**
     * Rangkum metrik bisnis harian ke dalam format array ringkas (token-efficient)
     * sebelum dikirim ke LLM API.
     *
     * @param string $tenantId
     * @param string|null $outletId
     * @param string|null $date (Format Y-m-d, default: hari ini)
     * @return array
     */
    public function aggregateDailyMetrics(string $tenantId, ?string $outletId = null, ?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        // Query dasar order paid untuk tenant & tanggal target
        $ordersQuery = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay]);

        if ($outletId) {
            $ordersQuery->where('outlet_id', $outletId);
        }

        $paidOrders = (clone $ordersQuery)->whereIn('status', ['PAID', 'paid'])->get();

        $totalRevenue = (float) $paidOrders->sum('final_amount');
        $transactionCount = $paidOrders->count();
        $averageBasketSize = $transactionCount > 0 ? round($totalRevenue / $transactionCount, 2) : 0;

        // Hitung total COGS menggunakan snapshot unit_cost dari order_items
        $paidOrderIds = $paidOrders->pluck('id');
        $totalCogs = 0.0;
        if ($paidOrderIds->isNotEmpty()) {
            $totalCogs = (float) OrderItem::whereIn('order_id', $paidOrderIds)
                ->sum(DB::raw('quantity * unit_cost'));
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // Metrik Keseluruhan (All-Time) untuk perbandingan
        $allTimeQuery = Order::where('tenant_id', $tenantId)->whereIn('status', ['PAID', 'paid']);
        if ($outletId) {
            $allTimeQuery->where('outlet_id', $outletId);
        }
        $allTimeRevenue = (float) $allTimeQuery->sum('final_amount');
        $allTimeTransactions = $allTimeQuery->count();

        // Top 5 Produk Terlaris
        $topProducts = [];
        if ($paidOrderIds->isNotEmpty()) {
            $topProducts = OrderItem::whereIn('order_id', $paidOrderIds)
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as total_qty'),
                    DB::raw('SUM(order_items.subtotal) as total_sales')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'name' => $item->name,
                    'qty' => (int) $item->total_qty,
                    'sales' => (float) $item->total_sales,
                ])
                ->toArray();
        }

        // Dead-stock / Produk dengan 0 penjualan hari ini
        $soldProductIds = [];
        if ($paidOrderIds->isNotEmpty()) {
            $soldProductIds = OrderItem::whereIn('order_id', $paidOrderIds)
                ->pluck('product_id')
                ->unique()
                ->toArray();
        }

        $deadStockProducts = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotIn('id', $soldProductIds)
            ->limit(5)
            ->pluck('name')
            ->toArray();

        // Bahan baku yang stoknya di bawah batas aman (Critical Stock)
        $stocksQuery = OutletStock::whereHas('rawMaterial', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->with('rawMaterial');

        if ($outletId) {
            $stocksQuery->where('outlet_id', $outletId);
        }

        $lowStockRawMaterials = $stocksQuery->get()
            ->filter(fn ($stock) => (float) $stock->current_stock <= (float) $stock->rawMaterial->minimum_stock)
            ->take(5)
            ->map(fn ($stock) => [
                'name' => $stock->rawMaterial->name,
                'current' => (float) $stock->current_stock,
                'min' => (float) $stock->rawMaterial->minimum_stock,
                'unit' => $stock->rawMaterial->unit,
            ])
            ->values()
            ->toArray();

        // Metrik Void / Pembatalan untuk deteksi fraud
        $voidOrders = (clone $ordersQuery)->whereIn('status', ['VOIDED', 'voided'])->get();
        $voidCount = $voidOrders->count();
        $voidAmount = (float) $voidOrders->sum('final_amount');

        return [
            'date' => $targetDate->format('Y-m-d'),
            'tenant_id' => $tenantId,
            'outlet_id' => $outletId,
            'financial' => [
                'total_revenue' => $totalRevenue,
                'total_cogs' => round($totalCogs, 2),
                'gross_profit' => round($grossProfit, 2),
                'profit_margin_pct' => $profitMargin,
                'transactions' => $transactionCount,
                'aov' => $averageBasketSize,
                'all_time_revenue' => $allTimeRevenue,
                'all_time_transactions' => $allTimeTransactions,
            ],
            'top_selling' => $topProducts,
            'slow_moving' => $deadStockProducts,
            'critical_stock' => $lowStockRawMaterials,
            'anomalies' => [
                'void_count' => $voidCount,
                'void_amount' => $voidAmount,
            ],
        ];
    }
}
