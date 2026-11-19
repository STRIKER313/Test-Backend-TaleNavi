<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(api: __DIR__.'/../routes/api.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $exception->errors(),
                ], $exception->status);
            }

            $status = match (true) {
                $exception instanceof AuthenticationException => 401,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };
            $message = match ($status) {
                401 => 'Token akses tidak ada, tidak valid, atau kedaluwarsa.',
                403 => 'Akses ditolak.',
                404 => 'Data atau endpoint tidak ditemukan.',
                405 => 'Metode HTTP tidak diizinkan.',
                409 => $exception->getMessage(),
                429 => 'Terlalu banyak permintaan.',
                default => $status >= 500 ? 'Terjadi kesalahan pada server.' : 'Permintaan gagal.',
            };
            $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

            return response()->json(['success' => false, 'message' => $message], $status, $headers);
        });
    })
    ->create();
