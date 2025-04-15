<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        \Log::info('DB error captured from withExceptions');
        $exceptions->render(function (PDOException | \Illuminate\Database\QueryException $e, $request) {
            return response()->json([
                'message' => 'Hubo un error inesperado. Inténtalo más tarde o contacta con soporte técnico para saber más.'
            ], 500);
        });
    })->create();
