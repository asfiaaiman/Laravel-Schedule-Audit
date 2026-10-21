<?php

declare(strict_types=1);

namespace AsfiaAiman\LaravelScheduleAudit\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

final class ScheduleAuditCommand extends Command
{
    protected $signature = 'schedule:audit';

    protected $description = 'Audit Laravel scheduled tasks for common risks.';

    public function handle(Schedule $schedule): int
    {
        $findings = [];

        foreach ($schedule->events() as $event) {
            if ($event->expression === '* * * * *' && ! $event->withoutOverlapping) {
                $findings[] = [
                    'Schedule' => $event->expression,
                    'Task' => $event->description ?? $event->command ?? 'Unnamed task',
                    'Finding' => 'Runs every minute without overlap protection',
                ];
            }
        }

        if ($findings === []) {
            $this->info('No audit findings.');
            return self::SUCCESS;
        }

        $this->table(['Schedule', 'Task', 'Finding'], $findings);
        $this->warn(count($findings) . ' potential issue(s) found.');

        return self::FAILURE;
    }
}
