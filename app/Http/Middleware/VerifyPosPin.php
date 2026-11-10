<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPosPin
{
    /**
     * Handle an incoming request.
     * Memastikan sesi kasir POS telah diverifikasi dengan 4-Digit PIN.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Wajib sudah login akun dasar Laravel Breeze
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->guest(route('login'));
        }

        // 2. Cek apakah PIN kasir aktif sudah terverifikasi di session
        if (! session('pos_pin_verified')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'POS_PIN_LOCKED',
                    'message' => 'Terminal kasir terkunci. Masukkan 4-Digit PIN untuk melanjutkan.',
                ], 403);
            }

            // Jika route bukan halaman lock screen itu sendiri, redirect ke pin lock
            if (! $request->routeIs('pos.lock') && ! $request->routeIs('pos.unlock')) {
                return redirect()->route('pos.lock');
            }
        }

        return $next($request);
    }
}

