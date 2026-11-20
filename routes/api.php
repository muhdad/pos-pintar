<?php

use App\Http\Controllers\Api\PosSyncController;
use Illuminate\Support\Facades\Route;

Route::prefix('pos')->group(function () {
    // 1. Sinkronisasi Data & Offline Bootstrap
    Route::post('/sync', [PosSyncController::class, 'sync'])->name('api.pos.sync');
    Route::get('/bootstrap-data', [PosSyncController::class, 'bootstrapData'])->name('api.pos.bootstrap');
    Route::post('/verify-pin', [PosSyncController::class, 'verifyPin'])->name('api.pos.verify-pin');

    // 2. Open Bill (Hold Order) & Pelunasan
    Route::post('/hold-order', [PosSyncController::class, 'holdOrder'])->name('api.pos.hold-order');
    Route::get('/open-bills', [PosSyncController::class, 'openBills'])->name('api.pos.open-bills');
    Route::post('/open-bills/{orderId}/settle', [PosSyncController::class, 'settleOpenBill'])->name('api.pos.open-bills.settle');

    // 3. Void Transaksi dengan PIN Supervisor
    Route::post('/orders/{orderId}/void', [PosSyncController::class, 'voidOrder'])->name('api.pos.orders.void');

    // 4. Manajemen Shift & Kas Laci
    Route::post('/shifts/start', [PosSyncController::class, 'startShift'])->name('api.pos.shifts.start');
    Route::post('/shifts/close', [PosSyncController::class, 'closeShift'])->name('api.pos.shifts.close');
    Route::post('/shifts/petty-cash', [PosSyncController::class, 'recordPettyCash'])->name('api.pos.shifts.petty-cash');
});
