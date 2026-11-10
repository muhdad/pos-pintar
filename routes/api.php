<?php

use App\Http\Controllers\Api\PosSyncController;
use Illuminate\Support\Facades\Route;

Route::prefix('pos')->group(function () {
    Route::post('/sync', [PosSyncController::class, 'sync'])->name('api.pos.sync');
    Route::get('/bootstrap-data', [PosSyncController::class, 'bootstrapData'])->name('api.pos.bootstrap');
    Route::post('/verify-pin', [PosSyncController::class, 'verifyPin'])->name('api.pos.verify-pin');
});

