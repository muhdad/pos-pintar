<?php

use App\Http\Controllers\Backoffice\BackofficeController;
use App\Http\Controllers\Backoffice\CashflowController;
use App\Http\Controllers\Backoffice\UserController;
use App\Http\Controllers\Pos\PosTerminalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Root Route: Direct to Login, Cashier POS, or Dashboard
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'CASHIER') {
            return redirect()->route('pos.index');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Backoffice Routes (Protected by Auth & RBAC: Superadmin, Owner, Manager, Finance)
Route::middleware(['auth', 'verified', 'role:SUPERADMIN,OWNER,MANAGER,FINANCE'])->group(function () {
    Route::get('/dashboard', [BackofficeController::class, 'dashboard'])->name('dashboard');

    Route::prefix('backoffice')->name('backoffice.')->group(function () {
        // Katalog Produk & Kategori
        Route::get('/products', [BackofficeController::class, 'products'])->name('products');
        Route::post('/products', [BackofficeController::class, 'storeProduct'])->name('products.store');
        Route::put('/products/{product}', [BackofficeController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}', [BackofficeController::class, 'destroyProduct'])->name('products.destroy');
        Route::post('/products/{product}/toggle', [BackofficeController::class, 'toggleProduct'])->name('products.toggle');

        Route::post('/categories', [BackofficeController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [BackofficeController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [BackofficeController::class, 'destroyCategory'])->name('categories.destroy');

        // Bahan Baku, Stok Gudang & Stock Opname
        Route::get('/inventory', [BackofficeController::class, 'inventory'])->name('inventory');
        Route::post('/inventory/raw-material', [BackofficeController::class, 'storeRawMaterial'])->name('inventory.raw-material.store');
        Route::put('/inventory/raw-material/{rawMaterial}', [BackofficeController::class, 'updateRawMaterial'])->name('inventory.raw-material.update');
        Route::delete('/inventory/raw-material/{rawMaterial}', [BackofficeController::class, 'destroyRawMaterial'])->name('inventory.raw-material.destroy');
        Route::post('/inventory/restock', [BackofficeController::class, 'restockInventory'])->name('inventory.restock');
        Route::post('/inventory/opname', [BackofficeController::class, 'recordOpname'])->name('inventory.opname');
        Route::post('/inventory/ocr-scan', [BackofficeController::class, 'scanReceiptOcr'])->name('inventory.ocr-scan');
        Route::post('/inventory/ocr-confirm', [BackofficeController::class, 'confirmOcrRestock'])->name('inventory.ocr-confirm');

        // Riwayat Transaksi, Export CSV & Void
        Route::get('/orders', [BackofficeController::class, 'orders'])->name('orders');
        Route::get('/orders/export-csv', [BackofficeController::class, 'exportOrdersCsv'])->name('orders.export-csv');
        Route::post('/orders/{order}/void', [BackofficeController::class, 'voidOrderBackoffice'])->name('orders.void');

        // Modul Keuangan & Arus Kas (Hanya Superadmin, Owner, dan Finance)
        Route::middleware(['role:SUPERADMIN,OWNER,FINANCE'])->group(function () {
            Route::get('/cashflow', [CashflowController::class, 'index'])->name('cashflow.index');
            Route::post('/cashflow/expense', [CashflowController::class, 'storeExpense'])->name('cashflow.expense');
            Route::post('/cashflow/income', [CashflowController::class, 'storeIncome'])->name('cashflow.income');
            Route::delete('/cashflow/{cashflow}', [CashflowController::class, 'destroy'])->name('cashflow.destroy');
        });

        // AI Business Consultant
        Route::get('/ai-consultant', [BackofficeController::class, 'aiConsultant'])->name('ai-consultant');
        Route::post('/ai-consultant/generate', [BackofficeController::class, 'triggerAiReport'])->name('ai-consultant.generate');

        // Reset Data Dummy
        Route::post('/reset-dummy-data', [BackofficeController::class, 'resetDummyData'])->name('reset-dummy-data');

        // Export & Import Data Katalog, Bahan Baku, dan Resep
        Route::get('/catalog/export', [BackofficeController::class, 'exportCatalog'])->name('catalog.export');
        Route::post('/catalog/import', [BackofficeController::class, 'importCatalog'])->name('catalog.import');

        // Manajemen Akun & Pengguna (Superadmin / Owner)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    });
});

// POS Terminal Route (Fullscreen Layout)
Route::middleware(['auth'])->prefix('pos')->group(function () {
    Route::get('/', [PosTerminalController::class, 'index'])->name('pos.index');
    Route::get('/customer-display', [PosTerminalController::class, 'customerDisplay'])->name('pos.customer-display');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
