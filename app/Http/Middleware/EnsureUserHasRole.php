<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Superadmin selalu memiliki akses penuh ke seluruh modul
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Periksa apakah role pengguna terdaftar dalam role yang diizinkan
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Respon untuk permintaan API / AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Akses ditolak. Fitur ini memerlukan otorisasi peran: ' . implode(', ', $roles),
            ], 403);
        }

        // Jika staf kasir mencoba mengakses Backoffice, arahkan langsung ke Terminal Kasir
        if ($user->role === 'CASHIER') {
            return redirect()->route('pos.index')
                ->with('error', 'Akses Backoffice terbatas untuk Owner dan Manager. Anda dialihkan ke Layar Kasir.');
        }

        abort(403, 'Anda tidak memiliki hak akses ke modul ini.');
    }
}
