# Laravel Schedule Audit

A lightweight CLI tool that audits Laravel scheduled tasks for common scheduling risks before deployment.

## Requirements

- PHP 8.2+
- Laravel with the task scheduler enabled

## Installation

```bash
composer require --dev asfiaaiman/laravel-schedule-audit
```

## Usage

Run the audit from your Laravel project:

```bash
php artisan schedule:audit
```

The command currently checks for scheduled tasks that run every minute without `withoutOverlapping()` protection.

Example finding:

```text
Runs every minute without overlap protection
```

A clean audit exits with status `0`. An audit with findings exits with status `1`, making the command suitable for CI pipelines.

## Scope and limitations

- Findings identify potential risks, not guaranteed bugs.
- Overlap protection is not appropriate for every scheduled task.
- The current version checks the exact `* * * * *` cron expression.
- This tool does not execute scheduled tasks or replace runtime monitoring.

## License

MIT