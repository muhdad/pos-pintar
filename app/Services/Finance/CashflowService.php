<?php

namespace App\Services\Finance;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CashflowService
{
    /**
     * Hitung ringkasan perputaran uang (Omzet H-1 dipotong pembelanjaan hari ini)
     * serta akumulasi saldo penarikan bulanan dan distribusi persentase Pie Chart.
     */
    public function getTurnoverMetrics(string $outletId, ?string $date = null): array
    {
        $today = $date ? Carbon::parse($date) : Carbon::today();
        $yesterday = (clone $today)->subDay();

        // 1. Omzet Kemarin (H-1) dari Order Selesai
        $omzetH1 = (float) Order::where('outlet_id', $outletId)
            ->whereDate('created_at', $yesterday)
            ->where('status', 'COMPLETED')
            ->sum('final_amount');

        // Cash vs Non-Cash H-1
        $omzetH1Cash = (float) Order::where('outlet_id', $outletId)
            ->whereDate('created_at', $yesterday)
            ->where('status', 'COMPLETED')
            ->whereHas('payments', fn ($q) => $q->where('method', 'CASH'))
            ->sum('final_amount');

        $omzetH1NonCash = max(0, $omzetH1 - $omzetH1Cash);

        // 2. Pembelanjaan Hari Ini (Outflows)
        $todayRestock = (float) Cashflow::where('outlet_id', $outletId)
            ->whereDate('transaction_date', $today)
            ->where('type', 'OUTFLOW')
            ->where('category', 'RESTOCK_PURCHASE')
            ->sum('amount');

        $todayOperational = (float) Cashflow::where('outlet_id', $outletId)
            ->whereDate('transaction_date', $today)
            ->where('type', 'OUTFLOW')
            ->whereIn('category', ['OPERATIONAL', 'PETTY_CASH'])
            ->sum('amount');

        $todayOtherExpense = (float) Cashflow::where('outlet_id', $outletId)
            ->whereDate('transaction_date', $today)
            ->where('type', 'OUTFLOW')
            ->whereNotIn('category', ['RESTOCK_PURCHASE', 'OPERATIONAL', 'PETTY_CASH'])
            ->sum('amount');

        $totalTodayExpense = $todayRestock + $todayOperational + $todayOtherExpense;

        // 3. Perputaran Uang H-1 vs Pengeluaran Hari Ini
        $sisaKasH1 = max(0, $omzetH1 - $totalTodayExpense);
        $defisitH1 = max(0, $totalTodayExpense - $omzetH1);
        $rasioBelanjaH1 = $omzetH1 > 0 ? round(($totalTodayExpense / $omzetH1) * 100, 1) : 0;

        // 4. Omzet Hari Ini (Hari H berjalan)
        $omzetHariIni = (float) Order::where('outlet_id', $outletId)
            ->whereDate('created_at', $today)
            ->where('status', 'COMPLETED')
            ->sum('final_amount');

        // 5. Akumulasi Omzet Tertahan Bulan Ini (Pencairan Bulanan / Monthly Withdrawal)
        // Karena sistem pencairan uang ditarik 1 bulan sekali, hitung total omzet bulan ini dikurangi total pembelanjaan
        $startOfMonth = (clone $today)->startOfMonth();
        $monthRevenue = (float) Order::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$startOfMonth->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->where('status', 'COMPLETED')
            ->sum('final_amount');

        $monthExpense = (float) Cashflow::where('outlet_id', $outletId)
            ->whereBetween('transaction_date', [$startOfMonth->toDateString(), $today->toDateString()])
            ->where('type', 'OUTFLOW')
            ->sum('amount');

        $retainedMonthlyFund = max(0, $monthRevenue - $monthExpense);

        // 6. Data Persentase Pie Chart (Distribusi Perputaran Kas)
        // Hitung persentase dari Total Penggunaan Dana (Belanja Bahan + Operasional + Sisa Kas H-1)
        $baseTotalPie = $omzetH1 > 0 ? $omzetH1 : ($totalTodayExpense > 0 ? $totalTodayExpense : 1);
        
        $pctRestock = $baseTotalPie > 0 ? round(($todayRestock / $baseTotalPie) * 100, 1) : 0;
        $pctOperational = $baseTotalPie > 0 ? round((($todayOperational + $todayOtherExpense) / $baseTotalPie) * 100, 1) : 0;
        $pctRetained = max(0, round(100 - ($pctRestock + $pctOperational), 1));

        // SVG Donut Calculation parameters (stroke-dasharray based on circumference 314.16 for r=50)
        $circumference = 314.16;
        $strokeRestock = ($pctRestock / 100) * $circumference;
        $strokeOperational = ($pctOperational / 100) * $circumference;
        $strokeRetained = ($pctRetained / 100) * $circumference;

        return [
            'today_date' => $today->translatedFormat('d M Y'),
            'yesterday_date' => $yesterday->translatedFormat('d M Y'),
            'omzet_h1' => $omzetH1,
            'omzet_h1_cash' => $omzetH1Cash,
            'omzet_h1_non_cash' => $omzetH1NonCash,
            'today_restock' => $todayRestock,
            'today_operational' => $todayOperational,
            'today_other_expense' => $todayOtherExpense,
            'total_today_expense' => $totalTodayExpense,
            'sisa_kas_h1' => $sisaKasH1,
            'defisit_h1' => $defisitH1,
            'rasio_belanja_h1' => $rasioBelanjaH1,
            'omzet_hari_ini' => $omzetHariIni,
            'month_revenue' => $monthRevenue,
            'month_expense' => $monthExpense,
            'retained_monthly_fund' => $retainedMonthlyFund,
            'pie_chart' => [
                'restock' => [
                    'label' => 'Belanja Bahan Baku (Restock)',
                    'amount' => $todayRestock,
                    'percentage' => $pctRestock,
                    'color' => '#1D4ED8', // Bauhaus Cobalt Blue
                    'stroke_length' => $strokeRestock,
                    'stroke_offset' => 0,
                ],
                'operational' => [
                    'label' => 'Operasional & Kas Kecil',
                    'amount' => $todayOperational + $todayOtherExpense,
                    'percentage' => $pctOperational,
                    'color' => '#DC2626', // Bauhaus Crimson Red
                    'stroke_length' => $strokeOperational,
                    'stroke_offset' => -$strokeRestock,
                ],
                'retained' => [
                    'label' => 'Sisa Kas Omzet H-1',
                    'amount' => $sisaKasH1,
                    'percentage' => $pctRetained,
                    'color' => '#FACC15', // Bauhaus Canary Yellow
                    'stroke_length' => $strokeRetained,
                    'stroke_offset' => -($strokeRestock + $strokeOperational),
                ],
                'circumference' => $circumference,
            ],
        ];
    }

    /**
     * Catat Pengeluaran Kas (Outflow).
     */
    public function recordExpense(string $tenantId, string $outletId, ?int $userId, array $data): Cashflow
    {
        return DB::transaction(function () use ($tenantId, $outletId, $userId, $data) {
            return Cashflow::create([
                'tenant_id' => $tenantId,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'type' => 'OUTFLOW',
                'category' => $data['category'] ?? 'OPERATIONAL',
                'amount' => (float) $data['amount'],
                'source_account' => $data['source_account'] ?? 'CASH_DRAWER',
                'transaction_date' => $data['transaction_date'] ?? Carbon::today()->toDateString(),
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'],
                'receipt_image_path' => $data['receipt_image_path'] ?? null,
                'is_h_minus_one_deduction' => $data['is_h_minus_one_deduction'] ?? true,
            ]);
        });
    }

    /**
     * Catat Pemasukan Kas Manual (Inflow: Setoran Modal, Penyesuaian Kas, dll.).
     */
    public function recordIncome(string $tenantId, string $outletId, ?int $userId, array $data): Cashflow
    {
        return DB::transaction(function () use ($tenantId, $outletId, $userId, $data) {
            return Cashflow::create([
                'tenant_id' => $tenantId,
                'outlet_id' => $outletId,
                'user_id' => $userId,
                'type' => 'INFLOW',
                'category' => $data['category'] ?? 'CAPITAL_IN',
                'amount' => (float) $data['amount'],
                'source_account' => $data['source_account'] ?? 'CASH_DRAWER',
                'transaction_date' => $data['transaction_date'] ?? Carbon::today()->toDateString(),
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'],
                'receipt_image_path' => $data['receipt_image_path'] ?? null,
                'is_h_minus_one_deduction' => false,
            ]);
        });
    }

    /**
     * Ambil Riwayat Mutasi Kas dengan Filter & Pagination.
     */
    public function getMutationList(string $outletId, array $filters = []): LengthAwarePaginator
    {
        $query = Cashflow::with('user')
            ->where('outlet_id', $outletId);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('transaction_date', [$filters['start_date'], $filters['end_date']]);
        } elseif (! empty($filters['date'])) {
            $query->whereDate('transaction_date', $filters['date']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
