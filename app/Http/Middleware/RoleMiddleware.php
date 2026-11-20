<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            Log::warning('Akses role ditolak', [
                'user_id' => $user->getAuthIdentifier(),
                'role' => $user->role ?? null,
                'allowed_roles' => $roles,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                ], 403);
            }

            abort(403, 'ANDA TIDAK MEMILIKI AKSES');
        }

        return $next($request);
    }
}
