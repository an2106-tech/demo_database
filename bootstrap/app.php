<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        if (getenv('RAILWAY_PROJECT_ID')) {
            $middleware->trustProxies(at: '*');
        }

        $middleware->alias([
            'candidate.account' => \App\Http\Middleware\EnsureCandidateAccount::class,
            'candidate.profile.complete' => \App\Http\Middleware\EnsureCandidateProfileComplete::class,
            'employer.account' => \App\Http\Middleware\EnsureEmployerAccount::class,
            'applications.kanban-only' => \App\Http\Middleware\RedirectLegacyApplicationPipeline::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
