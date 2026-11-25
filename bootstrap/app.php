<?php

use App\Http\Middleware\BlockSensitiveFiles;
use App\Http\Middleware\SecurityHeaders;
use App\Providers\ViewServiceProvider;
use App\Services\SecurityAuditLogger;
use App\Support\SensitiveDataSanitizer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        ViewServiceProvider::class,
    ])->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->prepend([
            SecurityHeaders::class,
            BlockSensitiveFiles::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Jalankan setelah StartSession agar idle & absolute timeout
        // diperiksa pada setiap request web yang terautentikasi.
        $middleware->web(append: [
            \App\Http\Middleware\EnforceSessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Biarkan ValidationException ditangani secara normal (error form 422)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            // Biarkan AuthenticationException dialihkan ke login
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();

                // 403 dicatat terpusat agar 403 dari middleware, policy, maupun
                // abort(403) di controller memiliki audit trail yang sama.
                if ($status === 403) {
                    $user = null;
                    try {
                        $user = $request->user();
                    } catch (\Throwable) {
                        // Jangan biarkan kegagalan resolusi user menutupi 403.
                    }

                    SecurityAuditLogger::logAccessDenied(
                        $user,
                        $request,
                        SensitiveDataSanitizer::url($request->fullUrl())
                    );

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Anda tidak memiliki hak akses ke sumber daya ini.',
                        ], 403);
                    }

                    return response()->view('errors.403', [], 403);
                }

                // 4xx lain (404, 405, 419, 429, ...) harus tetap memakai
                // status aslinya, bukan berubah menjadi 500.
                if ($status < 500) {
                    return null;
                }
            }

            // Generate Incident ID unik untuk korelasi log
            $incidentId = 'ERR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            // Catat detail teknis ke file log server beserta Incident ID.
            // URL dan pesan exception disanitasi agar token/password/LDAP
            //response tidak ikut masuk ke log.
            $userId = null;
            try {
                $userId = optional($request->user())->id;
            } catch (\Throwable) {
                // Error handler tidak boleh gagal hanya karena resolusi user.
            }

            Log::error(
                '[SYSTEM_ERROR] ID: '.$incidentId.' - '.SensitiveDataSanitizer::message($e->getMessage()),
                [
                    'incident_id' => $incidentId,
                    'exception'   => get_class($e),
                    'url'         => SensitiveDataSanitizer::url($request->fullUrl()),
                    'ip'          => $request->ip(),
                    'user_id'     => $userId,
                    'file'        => basename($e->getFile()) . ':' . $e->getLine(),
                ]
            );

            // Jika request berupa JSON / API
            if ($request->expectsJson()) {
                return response()->json([
                    'status'      => 'error',
                    'message'     => 'Terjadi kendala teknis pada sistem persuratan SAKTI. Permintaan tidak dapat diproses.',
                    'incident_id' => $incidentId,
                ], 500);
            }

            // Jangan bergantung pada view error kustom; error handler harus tetap
            // bisa merender respons meski view aplikasi gagal.
            return response(
                'Terjadi kendala teknis. Laporkan ID kejadian: '.$incidentId,
                500
            );
        });

        $exceptions->respond(function (Response $response): Response {
            return SecurityHeaders::apply($response);
        });
    })->create();
