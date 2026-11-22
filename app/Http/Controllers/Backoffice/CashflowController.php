<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Services\Finance\CashflowService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
}
