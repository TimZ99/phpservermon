<?php

use App\Enums\QueueName;
use App\Jobs\Heartbeat\CurlWorkerHeartbeat;
use App\Jobs\Maintenance\PruneCheckHistory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

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
    ->withSchedule(function (Schedule $schedule) {
        if (config('queue.default') === 'database') {
            $schedule->job(new CurlWorkerHeartbeat, QueueName::CURL->value)
                ->name('curl-heartbeat')
                ->description('Confirms the curl queue is being processed')
                ->everyFiveMinutes();
        }

        $schedule->job(new PruneCheckHistory, QueueName::MAINTENANCE->value)
            ->name('prune-check-history')
            ->description('Prunes old check history records')
            ->dailyAt('00:30');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
