<?php

namespace App\Services;

use App\Models\User;
use App\Support\SensitiveDataSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityAuditLogger
{
    /**
     * Catat event keamanan login berhasil.
     */
    public static function logLoginSuccess(User $user, Request $request): void
    {
        Log::info('[SECURITY_AUDIT] Login Berhasil', [
            'event'      => 'AUTH_LOGIN_SUCCESS',
            'timestamp'  => now()->toIso8601String(),
            'identifier' => SensitiveDataSanitizer::identifier($user->npm ?? $user->nidn ?? $user->username),
            'ip_address' => $request->ip(),
            'action'     => 'AUTH_LOGIN',
            'result'     => 'SUCCESS',
            'role'       => $user->role,
            'user_agent' => substr((string) $request->userAgent(), 0, 150),
        ]);
    }

    /**
     * Catat event keamanan login gagal.
     * Pastikan PASSWORD dan KREDENSIAL TIDAK PERNAH DICATAT.
     */
    public static function logLoginFailed(?string $identifier, Request $request, string $reason = 'Kredensial tidak valid'): void
    {
        Log::warning('[SECURITY_AUDIT] Login Gagal', [
            'event'      => 'AUTH_LOGIN_FAILED',
            'timestamp'  => now()->toIso8601String(),
            'identifier' => SensitiveDataSanitizer::identifier($identifier) ?: 'UNKNOWN',
            'ip_address' => $request->ip(),
            'action'     => 'AUTH_LOGIN',
            'result'     => 'FAILED',
            'reason'     => SensitiveDataSanitizer::message($reason),
            'user_agent' => substr((string) $request->userAgent(), 0, 150),
        ]);
    }

    /**
     * Catat event keamanan logout.
     */
    public static function logLogout(?User $user, Request $request): void
    {
        Log::info('[SECURITY_AUDIT] Logout Pengguna', [
            'event'      => 'AUTH_LOGOUT',
            'timestamp'  => now()->toIso8601String(),
            'identifier' => SensitiveDataSanitizer::identifier($user ? ($user->npm ?? $user->nidn ?? $user->username) : 'GUEST'),
            'ip_address' => $request->ip(),
            'action'     => 'AUTH_LOGOUT',
            'result'     => 'SUCCESS',
        ]);
    }

    /**
     * Catat event keamanan akses rute terproteksi ditolak (HTTP 403).
     */
    public static function logAccessDenied(?User $user, Request $request, string $targetResource): void
    {
        Log::warning('[SECURITY_AUDIT] Akses Rute Ditolak (403)', [
            'event'           => 'ACCESS_DENIED_403',
            'timestamp'       => now()->toIso8601String(),
            'identifier'      => SensitiveDataSanitizer::identifier($user ? ($user->npm ?? $user->nidn ?? $user->username) : 'GUEST'),
            'ip_address'      => $request->ip(),
            'action'          => 'ACCESS_ROUTE',
            'result'          => 'DENIED_403',
            'target_resource' => SensitiveDataSanitizer::url($targetResource),
            'role'            => $user->role ?? 'none',
        ]);
    }

    /**
     * Catat event keamanan perubahan peran/hak akses (Role Change).
     */
    public static function logRoleChanged(
        User $admin,
        User $targetUser,
        string $oldRole,
        string $newRole,
        Request $request
    ): void {
        Log::notice('[SECURITY_AUDIT] Perubahan Peran Pengguna', [
            'event'       => 'ROLE_CHANGED',
            'timestamp'   => now()->toIso8601String(),
            'admin_actor' => SensitiveDataSanitizer::identifier($admin->npm ?? $admin->nidn ?? $admin->username),
            'target_user' => SensitiveDataSanitizer::identifier($targetUser->npm ?? $targetUser->nidn ?? $targetUser->username),
            'old_role'    => $oldRole,
            'new_role'    => $newRole,
            'ip_address'  => $request->ip(),
            'action'      => 'ROLE_CHANGE',
            'result'      => 'SUCCESS',
        ]);
    }
}
