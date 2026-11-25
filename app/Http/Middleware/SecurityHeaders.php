<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Pasang header keamanan HTTP standar pada setiap respons aplikasi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return self::apply($response);
    }

    /**
     * Terapkan header keamanan ke objek respons (dipakai juga oleh exception handler).
     */
    public static function apply(Response $response): Response
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
