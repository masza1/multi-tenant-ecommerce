<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Block admin subdomain globally
        $middleware->prepend(\App\Http\Middleware\BlockAdminSubdomain::class);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register named middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Log::debug('Configuring exception handling: ' . $exceptions::class);
        
        // Handle validation exceptions - return proper response for Inertia
        $exceptions->render(function (Illuminate\Validation\ValidationException $e, $request) {
            $isInertia = $request->header('X-Inertia') !== null;

            \Log::debug('ValidationException caught', [
                'isInertia' => $isInertia,
                'errors' => $e->errors(),
            ]);

            // For both Inertia and traditional requests, redirect back with errors
            // Inertia will automatically populate form.errors and trigger onError
            return back()
                ->withErrors($e->errors())
                ->withInput();
        });

        // Handle tenant not found exception
        $exceptions->render(function (Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e, $request) {
            $status = 404;
            $title = 'Toko Tidak Ditemukan';
            $message = 'Toko yang Anda cari tidak ada atau tidak dapat diakses.';

            // Return JSON for API requests
            if ($request->expectsJson() && $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $title,
                ], $status);
            }

            // Return simple 404 page
            return \Inertia\Inertia::render('Errors/Error404', [
                'title' => $title,
                'message' => $message,
            ])->toResponse($request)->setStatusCode($status);
        });

        // Handle 404 Not Found exception specifically
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            // Return JSON for API requests
            if ($request->expectsJson() && $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found',
                ], 404);
            }

            // Return simple 404 page
            return \Inertia\Inertia::render('Errors/Error404', [
                'title' => 'Halaman Tidak Ditemukan',
                'message' => 'Halaman yang Anda cari tidak ada atau telah dipindahkan.',
            ])->toResponse($request)->setStatusCode(404);
        });

        // Render Inertia error pages for common HTTP errors
        $exceptions->render(function (Throwable $e, $request) {
            // Get status code
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            // In debug mode, show original error message
            if (config('app.debug')) {
                $title = get_class($e);
                $message = $e->getMessage() ?: 'No error message provided';
            } else {
                // Define error messages based on status code (Indonesian translations)
                $errors = [
                    400 => [
                        'title' => 'Permintaan Tidak Valid',
                        'message' => 'Permintaan Anda tidak dapat diproses. Silakan periksa kembali data yang Anda kirimkan.',
                    ],
                    401 => [
                        'title' => 'Tidak Terautentikasi',
                        'message' => 'Anda harus masuk terlebih dahulu untuk mengakses halaman ini.',
                    ],
                    403 => [
                        'title' => 'Akses Ditolak',
                        'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
                    ],
                    404 => [
                        'title' => 'Halaman Tidak Ditemukan',
                        'message' => 'Halaman yang Anda cari tidak ada atau telah dipindahkan.',
                    ],
                    419 => [
                        'title' => 'Sesi Kadaluarsa',
                        'message' => 'Sesi Anda telah kadaluarsa. Silakan refresh halaman dan coba lagi.',
                    ],
                    422 => [
                        'title' => 'Data Tidak Valid',
                        'message' => 'Data yang Anda kirimkan tidak memenuhi persyaratan. Silakan periksa kembali form Anda.',
                    ],
                    429 => [
                        'title' => 'Terlalu Banyak Permintaan',
                        'message' => 'Anda telah mengirim terlalu banyak permintaan. Silakan tunggu beberapa saat.',
                    ],
                    500 => [
                        'title' => 'Kesalahan Server Internal',
                        'message' => 'Terjadi kesalahan pada server. Tim kami sedang menangani masalah ini.',
                    ],
                    502 => [
                        'title' => 'Bad Gateway',
                        'message' => 'Server sedang dalam pemeliharaan. Silakan coba lagi nanti.',
                    ],
                    503 => [
                        'title' => 'Layanan Tidak Tersedia',
                        'message' => 'Server sedang dalam pemeliharaan. Silakan coba lagi nanti.',
                    ],
                    504 => [
                        'title' => 'Gateway Timeout',
                        'message' => 'Permintaan Anda memakan waktu terlalu lama. Silakan coba lagi.',
                    ],
                ];

                $errorInfo = $errors[$status] ?? [
                    'title' => 'Terjadi Kesalahan',
                    'message' => 'Terjadi kesalahan yang tidak terduga. Silakan hubungi support.',
                ];

                $title = $errorInfo['title'];
                $message = $errorInfo['message'];
            }

            // Return JSON only for API requests (not Inertia)
            if ($request->expectsJson() && $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $title,
                    'debug' => config('app.debug') ? $message : null,
                ], $status);
            }

            // Use Inertia to render error page for all web requests
            // Inertia responses must be returned directly, not wrapped
            return \Inertia\Inertia::render('Errors/Error', [
                'status' => $status,
                'title' => $title,
                'message' => $message,
                'stack' => config('app.debug') ? collect(explode("\n", $e->getTraceAsString())) : null,
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
