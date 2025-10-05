<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\BlockSuspendedUsers; // 👈 nuestro middleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Que corra primero en el stack web (bloquea antes de ejecutar nada más)
        $middleware->web(prepend: [
            BlockSuspendedUsers::class,
        ]);

        // Si preferís también global, podés mantener:
        // $middleware->append(BlockSuspendedUsers::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
