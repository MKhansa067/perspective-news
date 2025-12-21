<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <--- PENTING: Import Request
use App\Http\Middleware\IsAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // 1. Agar Railway (Load Balancer) dipercaya oleh Laravel
        // Ini mengatasi masalah redirect HTTPS dan Session hilang
        $middleware->trustProxies(at: '*');

        // 2. Daftarkan alias Middleware
        $middleware->alias([
            'is_admin' => IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();