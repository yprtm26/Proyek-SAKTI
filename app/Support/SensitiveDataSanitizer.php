<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Utility kecil untuk menjaga data sensitif tidak masuk ke log.
 *
 * Yang disanitasi:
 * - query string dan fragment URL (tempat token/code sering berada),
 * - kredensial yang menempel di URL,
 * - nilai setelah kata kunci sensitif di dalam pesan exception,
 * - baris baru agar log tetap satu baris.
 */
final class SensitiveDataSanitizer
{
    private const REDACTED = '[REDACTED]';

    public static function url(string $url): string
    {
        // Jangan pernah menyimpan user:password yang menempel pada URL.
        $url = preg_replace('#//[^/@\s]+@#', '//'.self::REDACTED.'@', $url) ?? $url;

        // Query string dan fragment dibuang seluruhnya. Path tetap disimpan
        // agar insiden masih bisa ditelusuri tanpa membocorkan token.
        return preg_replace('/[?#].*$/', '', $url) ?? $url;
    }

    public static function message(string $message): string
    {
        $message = preg_replace('/[\r\n\t]+/', ' ', $message) ?? $message;

        $pattern = '/(password|passwd|pwd|secret|token|api[_-]?key|authorization|cookie|session|credential|bind[_-]?password)\s*[:=]\s*[^\s,;]+/i';

        $message = preg_replace($pattern, '$1='.self::REDACTED, $message) ?? $message;

        return Str::limit(trim($message), 500);
    }

    public static function identifier(?string $identifier): string
    {
        $identifier = preg_replace('/[\r\n\t]+/', ' ', (string) $identifier) ?? '';

        return Str::limit(trim($identifier), 100);
    }
}
