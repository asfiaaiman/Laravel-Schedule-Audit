<?php
declare(strict_types=1);

namespace AsfiaAiman\LaravelScheduleAudit;

use AsfiaAiman\LaravelScheduleAudit\Commands\ScheduleAuditCommand;
use Illuminate\Support\ServiceProvider;

final class LaravelScheduleAuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScheduleAuditCommand::class,
            ]);
        }
    }
}
