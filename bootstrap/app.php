<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\SyncFootballDataAll::class,
        \App\Console\Commands\SyncFootballDataFixtures::class,
        \App\Console\Commands\SyncFootballDataLive::class,
        \App\Console\Commands\SyncFootballDataResults::class,
        \App\Console\Commands\SyncFootballDataTeams::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'invite.access' => \App\Http\Middleware\EnsureInviteAccessAllowed::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
