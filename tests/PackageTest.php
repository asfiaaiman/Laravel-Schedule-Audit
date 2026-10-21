<?php
declare(strict_types=1);

namespace AsfiaAiman\LaravelScheduleAudit\Tests;

use AsfiaAiman\LaravelScheduleAudit\LaravelScheduleAuditServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\TestCase;

final class PackageTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaravelScheduleAuditServiceProvider::class];
    }

    public function test_it_reports_every_minute_tasks_without_overlap_protection(): void
    {
        $this->app->make(Schedule::class)->command('inspire')->everyMinute();

        $this->artisan('schedule:audit')
            ->expectsOutputToContain('Runs every minute without overlap protection')
            ->assertExitCode(1);
    }

    public function test_it_does_not_report_protected_every_minute_tasks(): void
    {
        $this->app->make(Schedule::class)->command('inspire')->everyMinute()->withoutOverlapping();

        $this->artisan('schedule:audit')
            ->expectsOutput('No audit findings.')
            ->assertExitCode(0);
    }

    public function test_service_provider_is_registered(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(LaravelScheduleAuditServiceProvider::class));
    }

    public function test_schedule_audit_command_is_registered(): void
    {
        $this->assertArrayHasKey('schedule:audit', Artisan::all());
    }

    public function test_it_does_not_report_tasks_that_run_every_five_minutes(): void
    {
        $this->app->make(Schedule::class)->command('inspire')->everyFiveMinutes();

        $this->artisan('schedule:audit')
            ->expectsOutput('No audit findings.')
            ->assertExitCode(0);
    }

    public function test_it_reports_multiple_unprotected_every_minute_tasks(): void
    {
        $schedule = $this->app->make(Schedule::class);

        $schedule->command('inspire')->everyMinute();
        $schedule->command('about')->everyMinute();

        $this->artisan('schedule:audit')
            ->expectsOutputToContain('2 potential issue(s) found.')
            ->assertExitCode(1);
    }
}
