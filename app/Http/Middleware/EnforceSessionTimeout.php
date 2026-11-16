<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionTimeout
{
    /**
     * Kunci sesi yang tidak boleh pernah ada di sesi pengguna.
     *
     * @var array<int, string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'credentials',
        'ldap_password',
        'ldap_credentials',
        'bind_password',
        'userpassword',
    ];

    /**
     * Terapkan idle timeout dan absolute timeout pada setiap request web.
     *
     * - Idle timeout   : SESSION_LIFETIME (default 30 menit).
     * - Absolute timeout: SESSION_ABSOLUTE_LIFETIME (default 480 menit / 8 jam).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! Auth::guard('web')->check()) {
            return $next($request);
        }

        $session = $request->session();

        // Pastikan data sensitif tidak pernah bertahan di sesi.
        $session->forget(self::SENSITIVE_KEYS);

        $now = now()->getTimestamp();

        $loginAt = (int) $session->get('auth_login_at', 0);
        $lastActivity = (int) $session->get('auth_last_activity', 0);

        // Sesi lama (sebelum fitur ini dipasang) atau sesi yang dibuat lewat
        // actingAs() belum memiliki timestamp. Mulai hitungan dari sekarang
        // agar tetap aman dan tidak langsung memaksa logout.
        if ($loginAt <= 0) {
            $loginAt = $now;
            $session->put('auth_login_at', $loginAt);
        }

        if ($lastActivity <= 0) {
            $lastActivity = $now;
            $session->put('auth_last_activity', $lastActivity);
        }

        $idleMinutes = max(1, (int) config('session.lifetime', 30));
        $absoluteMinutes = max(1, (int) config('session.absolute_lifetime', 480));

        $idleSeconds = $idleMinutes * 60;
        $absoluteSeconds = $absoluteMinutes * 60;

        $idleExpired = ($now - $lastActivity) >= $idleSeconds;
        $absoluteExpired = ($now - $loginAt) >= $absoluteSeconds;

        if ($idleExpired || $absoluteExpired) {
            $reason = $absoluteExpired ? 'absolute_timeout' : 'idle_timeout';

            $absoluteHours = $absoluteMinutes / 60;
            $absoluteLabel = $absoluteMinutes % 60 === 0
                ? $absoluteHours.' jam'
                : $absoluteMinutes.' menit';

            $message = $absoluteExpired
                ? "Sesi Anda telah berakhir karena mencapai batas maksimal {$absoluteLabel}. Silakan login kembali."
                : "Sesi Anda berakhir karena tidak ada aktivitas selama {$idleMinutes} menit. Silakan login kembali.";

            Log::info('Sesi pengguna diakhiri otomatis', [
                'reason' => $reason,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);

            Auth::guard('web')->logout();
            $session->invalidate();
            $session->regenerateToken();

            // Queue (bukan withCookie) agar cookie kedaluwarsa ditambahkan
            // setelah StartSession menulis cookie sesi baru pada response.
            Cookie::queue(
                Cookie::forget(
                    config('session.cookie'),
                    config('session.path'),
                    config('session.domain')
                )
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'code' => $reason,
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('status', $message);
        }

        // Perbarui waktu aktivitas terakhir untuk perhitungan idle timeout.
        $session->put('auth_last_activity', $now);

        return $next($request);
    }
}
