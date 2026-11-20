<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SecurityAuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Batasi akses route hanya untuk role tertentu.
     *
     * Penggunaan:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,kaprodi,dekan')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login → arahkan ke halaman login.
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Silakan login terlebih dahulu.',
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Role user tidak termasuk role yang diizinkan.
        if (! $user instanceof User || ! $user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                SecurityAuditLogger::logAccessDenied($user, $request, $request->fullUrl());

                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                ], 403);
            }

            // Untuk HTML, audit 403 dilakukan terpusat di bootstrap/app.php
            // agar 403 dari policy/controller juga tercatat.
            abort(403, 'ANDA TIDAK MEMILIKI AKSES');
        }

        return $next($request);
    }
}
