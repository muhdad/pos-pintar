<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Services\Export\ExcelExportService;
use App\Services\Finance\CashflowService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashflowController extends Controller
{
    public function __construct(
        protected CashflowService $cashflowService
    ) {}

    /**
     * Dashboard Arus Kas & Analisis Perputaran Uang (Omzet H-1 vs Belanja Hari Ini).
     * Terbatas hanya untuk SUPERADMIN, OWNER, dan FINANCE.
     */
    public function index(Request $request): View
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $metrics = $this->cashflowService->getTurnoverMetrics($outlet->id, $selectedDate);

        $filters = [
            'date' => $request->input('filter_date'),
            'type' => $request->input('type'),
            'category' => $request->input('category'),
            'search' => $request->input('search'),
        ];

        $mutations = $this->cashflowService->getMutationList($outlet->id, $filters);

        return view('backoffice.cashflow.index', compact(
            'tenant',
            'outlet',
            'metrics',
            'mutations',
            'selectedDate',
            'filters'
        ));
    }

    /**
     * Catat Pengeluaran Baru (Belanja Bahan, Operasional, Petty Cash, Gaji, dll.).
     */
    public function storeExpense(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $validated = $request->validate([
            'category' => 'required|string|in:RESTOCK_PURCHASE,OPERATIONAL,PETTY_CASH,SALARY,RENT_UTILITIES,OWNER_WITHDRAWAL,OTHER',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'source_account' => 'required|string|in:CASH_DRAWER,PETTY_CASH,BANK_ACCOUNT',
            'transaction_date' => 'required|date',
            'receipt_image' => 'nullable|image|max:5120',
            'is_h_minus_one_deduction' => 'nullable',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $filename = 'expense_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/receipts');
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $receiptPath = 'uploads/receipts/' . $filename;
        }

        $this->cashflowService->recordExpense(
            $tenant->id,
            $outlet->id,
            $request->user()?->id,
            [
                'category' => $validated['category'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'source_account' => $validated['source_account'],
                'transaction_date' => $validated['transaction_date'],
                'receipt_image_path' => $receiptPath,
                'is_h_minus_one_deduction' => $request->boolean('is_h_minus_one_deduction', true),
            ]
        );

        return redirect()->route('backoffice.cashflow.index')
            ->with('status', 'Pengeluaran sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil dicatat!');
    }

    /**
     * Catat Pemasukan Kas Baru (Setoran Modal, Pendapatan Lain-lain, Penyesuaian Kas).
     */
    public function storeIncome(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $validated = $request->validate([
            'category' => 'required|string|in:CAPITAL_IN,SALES_CASH,SALES_NON_CASH,OTHER_INCOME',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'source_account' => 'required|string|in:CASH_DRAWER,PETTY_CASH,BANK_ACCOUNT',
            'transaction_date' => 'required|date',
        ]);

        $this->cashflowService->recordIncome(
            $tenant->id,
            $outlet->id,
            $request->user()?->id,
            [
                'category' => $validated['category'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'source_account' => $validated['source_account'],
                'transaction_date' => $validated['transaction_date'],
            ]
        );

        return redirect()->route('backoffice.cashflow.index')
            ->with('status', 'Pemasukan sebesar Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil dicatat!');
    }

    /**
     * Hapus Catatan Mutasi Kas.
     */
    public function destroy(Cashflow $cashflow): RedirectResponse
    {
        $desc = $cashflow->description;
        $amount = $cashflow->amount;
        $cashflow->delete();

        return redirect()->route('backoffice.cashflow.index')
            ->with('status', "Catatan mutasi '{$desc}' (Rp " . number_format($amount, 0, ',', '.') . ') berhasil dihapus.');
    }

    /**
     * Export Laporan Arus Kas & Keuangan ke File Excel (.xls).
     */
    public function exportExcel(Request $request, ExcelExportService $excelService): StreamedResponse
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();
        $outlet = $tenant?->outlets()->first() ?? Outlet::first();

        $query = Cashflow::with('user')
            ->where('outlet_id', $outlet->id);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }
        if ($date = $request->input('date')) {
            $query->whereDate('transaction_date', $date);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        $mutations = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalInflow = (float) $mutations->where('type', 'INFLOW')->sum('amount');
        $totalOutflow = (float) $mutations->where('type', 'OUTFLOW')->sum('amount');
        $netCash = $totalInflow - $totalOutflow;
        $totalH1Deduction = (float) $mutations->where('is_h_minus_one_deduction', true)->sum('amount');

        $headers = [
            'No',
            'Tanggal',
            'Waktu',
            'Tipe',
            'Kategori',
            'Nominal (Rp)',
            'Potong Omzet H-1',
            'Sumber Akun Kas',
            'Keterangan / Transaksi',
            'Dicatat Oleh'
        ];

        $categoryLabels = [
            'RESTOCK_PURCHASE' => 'Belanja Bahan Baku',
            'OPERATIONAL' => 'Operasional Resto',
            'SALARY' => 'Gaji & Upah Karyawan',
            'PETTY_CASH' => 'Kas Kecil Kasir',
            'CAPITAL_INJECTION' => 'Tambahan Modal Pemilik',
            'SALES_DEPOSIT' => 'Setoran Penjualan Kasir',
            'OTHER_INCOME' => 'Pemasukan Lainnya',
            'OTHER_EXPENSE' => 'Pengeluaran Lainnya',
        ];

        $rows = [];
        foreach ($mutations as $i => $m) {
            $isIncome = ($m->type === 'INFLOW');
            $rows[] = [
                ['value' => $i + 1, 'type' => 'center'],
                ['value' => $m->transaction_date ? date('d/m/Y', strtotime($m->transaction_date)) : '-', 'type' => 'center'],
                ['value' => $m->created_at ? $m->created_at->format('H:i') : '-', 'type' => 'center'],
                [
                    'value' => $isIncome ? 'PEMASUKAN' : 'PENGELUARAN',
                    'type' => $isIncome ? 'badge-in' : 'badge-out'
                ],
                ['value' => $categoryLabels[$m->category] ?? $m->category, 'type' => 'text'],
                [
                    'value' => (float) $m->amount,
                    'type' => 'num'
                ],
                [
                    'value' => $m->is_h_minus_one_deduction ? 'YA (Dipotong H-1)' : 'TIDAK',
                    'type' => $m->is_h_minus_one_deduction ? 'badge-warn' : 'center'
                ],
                ['value' => $m->source_account ?? 'CASH_DRAWER', 'type' => 'text'],
                ['value' => $m->description ?? '-', 'type' => 'text'],
                ['value' => $m->user?->name ?? 'Admin', 'type' => 'text'],
            ];
        }

        $totals = [
            ['value' => 'TOTAL', 'type' => 'center'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => 'Kas Bersih: ' . ($netCash >= 0 ? '+' : '-') . 'Rp ' . number_format(abs($netCash), 0, ',', '.'), 'type' => 'text'],
            ['value' => (float) $netCash, 'type' => 'currency'],
            ['value' => 'Dipotong H-1: Rp ' . number_format($totalH1Deduction, 0, ',', '.'), 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
            ['value' => '', 'type' => 'text'],
        ];

        $summary = [
            'Total Pemasukan' => 'Rp ' . number_format($totalInflow, 0, ',', '.'),
            'Total Pengeluaran' => 'Rp ' . number_format($totalOutflow, 0, ',', '.'),
            'Saldo Kas Bersih' => ($netCash >= 0 ? '+' : '-') . 'Rp ' . number_format(abs($netCash), 0, ',', '.'),
            'Total Belanja Potong H-1' => 'Rp ' . number_format($totalH1Deduction, 0, ',', '.'),
        ];

        $filename = 'Laporan_Arus_Kas_' . date('Ymd_His') . '.xls';
        $title = 'LAPORAN ARUS KAS & KEUANGAN - ' . strtoupper($outlet->name ?? 'SOTO LAMONGAN LESTARI');
        $subtitle = 'Dicetak pada: ' . now()->translatedFormat('d F Y H:i') . ' WIB | Outlet: ' . ($outlet->name ?? 'Cabang Utama');

        return $excelService->download($filename, $title, $subtitle, $summary, $headers, $rows, $totals);
    }
}
