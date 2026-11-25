<?php

$allowedOriginsRaw = (string) env('CORS_ALLOWED_ORIGINS', '');
$allowedOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => rtrim(trim($origin), '/'),
    explode(',', $allowedOriginsRaw)
)));

if ($allowedOrigins === []) {
    $appUrl = rtrim(trim((string) env('APP_URL', 'http://localhost')), '/');
    if ($appUrl !== '') {
        $allowedOrigins = [$appUrl];
    }
}

// Pastikan wildcard '*' tidak pernah aktif meskipun salah konfigurasi di .env.
$allowedOrigins = array_values(array_filter(
    $allowedOrigins,
    static fn (string $origin): bool => $origin !== '*'
));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-CSRF-TOKEN', 'Accept', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
