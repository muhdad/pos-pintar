<?php

namespace App\Services\Pos;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShiftService
{
    /**
     * Dapatkan shift yang sedang aktif di outlet tertentu.
     */
    public function getActiveShift(string $outletId): ?Shift
    {
        return Shift::where('outlet_id', $outletId)
            ->whereNull('end_time')
            ->with(['user', 'terminal'])
            ->latest('start_time')
            ->first();
    }

    /**
     * Buka Shift Kasir Baru (Input Modal Awal Laci).
     */
    public function startShift(
        string $outletId,
        string $terminalId,
        int $userId,
        float $startCash,
        ?string $notes = null
    ): Shift {
        return DB::transaction(function () use ($outletId, $terminalId, $userId, $startCash, $notes) {
            $shift = Shift::create([
                'id' => (string) Str::uuid(),
                'outlet_id' => $outletId,
                'terminal_id' => $terminalId,
                'user_id' => $userId,
                'start_time' => now(),
                'start_cash' => $startCash,
                'notes' => $notes,
            ]);

            AuditLog::create([
                'tenant_id' => $shift->outlet->tenant_id ?? null,
                'user_id' => $userId,
                'action' => 'SHIFT_STARTED',
                'description' => "Shift kasir dibuka. Modal awal laci kasir: Rp" . number_format($startCash, 0, ',', '.'),
                'metadata' => [
                    'shift_id' => $shift->id,
                    'start_cash' => $startCash,
                    'terminal_id' => $terminalId,
                ],
            ]);

            return $shift;
        });
    }

    /**
     * Catat Pengeluaran Kas Kecil Operasional Harian (Petty Cash Out).
     */
    public function recordPettyCash(
        string $shiftId,
        float $amount,
        string $reason,
        int $userId
    ): AuditLog {
        return DB::transaction(function () use ($shiftId, $amount, $reason, $userId) {
            $shift = Shift::with('outlet')->findOrFail($shiftId);

            return AuditLog::create([
                'tenant_id' => $shift->outlet->tenant_id ?? null,
                'user_id' => $userId,
                'action' => 'PETTY_CASH_OUT',
                'description' => "Petty Cash Out (Kas Keluar): Rp" . number_format($amount, 0, ',', '.') . " - {$reason}",
                'metadata' => [
                    'shift_id' => $shiftId,
                    'amount' => $amount,
                    'reason' => $reason,
                    'outlet_id' => $shift->outlet_id,
                ],
            ]);
        });
    }

    /**
     * Tutup Shift Kasir (Blind Close: Kasir input uang fisik aktual di laci tanpa melihat ekspektasi dulu).
     */
    public function closeShift(
        string $shiftId,
        float $actualCash,
        ?string $notes = null
    ): Shift {
        return DB::transaction(function () use ($shiftId, $actualCash, $notes) {
            $shift = Shift::with('outlet')->findOrFail($shiftId);

            // 1. Total penerimaan uang tunai dari seluruh pembayaran pesanan shift ini
            $cashCollected = Payment::whereHas('order', function ($q) use ($shiftId) {
                $q->where('shift_id', $shiftId)->where('status', 'PAID');
            })->where('method', 'CASH')->sum('amount');

            // 2. Total pengeluaran kas kecil (Petty Cash Out) selama shift ini
            $pettyCashLogs = AuditLog::where('action', 'PETTY_CASH_OUT')
                ->where('created_at', '>=', $shift->start_time)
                ->get();

            $pettyCashOut = 0;
            foreach ($pettyCashLogs as $log) {
                if (isset($log->metadata['shift_id']) && $log->metadata['shift_id'] === $shiftId) {
                    $pettyCashOut += (float) ($log->metadata['amount'] ?? 0);
                }
            }

            // 3. Ekspektasi uang fisik di laci = Modal Awal + Tunai Masuk - Kas Kecil Keluar
            $expectedCash = (float) $shift->start_cash + (float) $cashCollected - (float) $pettyCashOut;
            $cashDifference = $actualCash - $expectedCash; // Negatif = Short (Kurang), Positif = Over (Lebih)

            $shift->update([
                'end_time' => now(),
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'cash_difference' => $cashDifference,
                'notes' => $notes ? ($shift->notes . " | Tutup: " . $notes) : $shift->notes,
            ]);

            AuditLog::create([
                'tenant_id' => $shift->outlet->tenant_id ?? null,
                'user_id' => $shift->user_id,
                'action' => 'SHIFT_CLOSED',
                'description' => sprintf(
                    "Shift kasir ditutup. Aktual: Rp%s, Ekspektasi: Rp%s, Selisih: Rp%s",
                    number_format($actualCash, 0, ',', '.'),
                    number_format($expectedCash, 0, ',', '.'),
                    number_format($cashDifference, 0, ',', '.')
                ),
                'metadata' => [
                    'shift_id' => $shift->id,
                    'start_cash' => (float) $shift->start_cash,
                    'cash_collected' => (float) $cashCollected,
                    'petty_cash_out' => (float) $pettyCashOut,
                    'expected_cash' => $expectedCash,
                    'actual_cash' => $actualCash,
                    'cash_difference' => $cashDifference,
                ],
            ]);

            return $shift;
        });
    }
}
