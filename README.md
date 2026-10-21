# Laravel Schedule Audit

A lightweight CLI tool that audits Laravel scheduled tasks for common scheduling risks before deployment.

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- A Laravel application that defines scheduled tasks

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

Example clean result:

```text
No audit findings.
```

Example finding summary:

```text
1 potential issue(s) found.
```

## Scope and limitations

- Findings identify potential risks, not guaranteed bugs.
- Overlap protection is not appropriate for every scheduled task.
- The current version checks the exact `* * * * *` cron expression.
- It cannot determine whether overlap protection is appropriate for a particular task. Tasks scheduled through other mechanisms, or every-minute tasks intentionally allowed to overlap, may need a manual review.
- It does not inspect task execution time, locks configured outside `withoutOverlapping()`, queue configuration, or runtime failures.
- This tool does not execute scheduled tasks or replace runtime monitoring.

## Testing locally

```bash
composer install
vendor/bin/phpunit
composer validate --strict
composer dump-autoload --optimize
```

## License

MIT
