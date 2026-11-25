<?php

namespace App\Http\Middleware;

use App\Services\SecurityAuditLogger;
use App\Support\SensitiveDataSanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSensitiveFiles
{
    /**
     * Ekstensi file sensitif (log, cadangan, database, konfigurasi) yang tidak boleh
     * pernah dapat diakses melalui HTTP.
     *
     * @var array<int, string>
     */
    private const BLOCKED_EXTENSIONS = [
        'log',
        'sql',
        'sqlite',
        'sqlite3',
        'db',
        'bak',
        'backup',
        'old',
        'orig',
        'save',
        'swp',
        'swo',
        'dist',
        'ini',
        'conf',
        'env',
        'key',
        'pem',
        'crt',
        'zip',
        'tar',
        'gz',
        '7z',
        'rar',
    ];

    /**
     * Berkas konfigurasi/manajemen proyek yang tidak boleh dibuka via browser.
     *
     * @var array<int, string>
     */
    private const BLOCKED_FILENAMES = [
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'phpunit.xml',
        'serve.bat',
        'auth.json',
    ];

    /**
     * Tolak akses ke file sensitif (/.env, /.git/*, cadangan, dan log) dengan HTTP 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (self::isSensitivePath($request->getPathInfo())) {
            $user = null;
            try {
                $user = $request->user();
            } catch (\Throwable) {
                // Abaikan kegagalan resolusi user saat memblokir path sensitif.
            }

            SecurityAuditLogger::logAccessDenied(
                $user,
                $request,
                SensitiveDataSanitizer::url($request->fullUrl())
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ke berkas sensitif ditolak.',
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }

    /**
     * Periksa apakah path HTTP mengarah ke dotfile, direktori VCS, log, atau file cadangan.
     */
    public static function isSensitivePath(string $rawPath): bool
    {
        $decoded = strtolower(rawurldecode($rawPath));
        $normalized = str_replace('\\', '/', $decoded);
        $segments = array_values(array_filter(
            explode('/', $normalized),
            static fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === []) {
            return false;
        }

        // Blokir semua dotfile/dotdir (/.env, /.git/config, /.htaccess) kecuali /.well-known
        foreach ($segments as $index => $segment) {
            if (str_starts_with($segment, '.') && ! ($index === 0 && $segment === '.well-known')) {
                return true;
            }
        }

        // Blokir akses ke direktori internal sensitif bila ada yang tersingkap
        if (
            ($segments[0] === 'storage' && isset($segments[1]) && $segments[1] === 'logs')
            || ($segments[0] === 'bootstrap' && isset($segments[1]) && $segments[1] === 'cache')
            || $segments[0] === 'database'
        ) {
            return true;
        }

        $lastSegment = $segments[count($segments) - 1];
        if (in_array($lastSegment, self::BLOCKED_FILENAMES, true)) {
            return true;
        }

        $extension = pathinfo($lastSegment, PATHINFO_EXTENSION);

        return $extension !== '' && in_array($extension, self::BLOCKED_EXTENSIONS, true);
    }
}
