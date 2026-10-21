# Laravel Schedule Audit

A lightweight Laravel package that identifies a common scheduling risk: tasks configured to run every minute without overlap protection.

## Features

- Detects scheduled tasks running every minute without `withoutOverlapping()` protection.
- Displays findings in a command-line table.
- Returns a non-zero exit code when potential issues are detected.
- Supports Laravel package auto-discovery.
- Does not modify your application's schedule.

## Requirements

- PHP 8.2 or later, subject to the selected Laravel version.
- Laravel 12 or Laravel 13.
- Composer.

## Installation

Install the package from [Packagist](https://packagist.org/packages/asfiaaiman/laravel-schedule-audit):

```bash
composer require --dev asfiaaiman/laravel-schedule-audit
```

Use `--dev` when the package is used for development or CI checks. If your production deployment needs to run the audit and excludes development dependencies, install it without `--dev`:

```bash
composer require asfiaaiman/laravel-schedule-audit
```

Laravel automatically discovers the package's service provider. No manual registration is required.

Verify the installation:

```bash
php artisan schedule:audit --help
```

## Usage

Run the audit from your Laravel application's root directory:

```bash
php artisan schedule:audit
```

### No findings

```text
No audit findings.
```

This means no events matched the current detection rule. It does not guarantee that every scheduled task is safe.

### Potential issue detected

Example output:

```text
+-----------------+----------------------+----------------------------------------------+
| Schedule        | Task                 | Finding                                      |
+-----------------+----------------------+----------------------------------------------+
| * * * * *       | reports:generate     | Runs every minute without overlap protection |
+-----------------+----------------------+----------------------------------------------+

1 potential issue(s) found.
```

The output depends on your application's registered scheduled tasks.

## What does it check?

The current version detects scheduled events that meet both conditions:

1. Their cron expression is exactly `* * * * *`, meaning every minute.
2. They do not have Laravel's `withoutOverlapping()` protection enabled.

For example:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:generate')
    ->everyMinute();
```

If this task takes longer than expected, overlapping executions may cause duplicated work or resource contention.

If concurrent execution is unsafe for this task, consider:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:generate')
    ->everyMinute()
    ->withoutOverlapping();
```

Laravel uses a cache-based mutex for overlap protection. Ensure your cache configuration supports the locking behavior your application needs.

Do not add overlap protection blindly. Review the task's behavior and any existing concurrency controls first.

See the [Laravel Task Scheduling documentation](https://laravel.com/docs/12.x/scheduling).

## Using it in real projects

### Local development

Run the audit after changing scheduled tasks:

```bash
php artisan schedule:audit
```

Review any findings and decide whether they require changes.

### Deployment checks

Run the audit as part of your deployment process:

```bash
php artisan schedule:audit
```

A non-zero exit code indicates findings or a command failure. Investigate before deciding whether deployment should proceed.

### GitHub Actions

After installing dependencies and configuring your Laravel application, add this step to your workflow:

```yaml
- name: Audit scheduled tasks
  run: php artisan schedule:audit
```

The step fails if the command returns a non-zero exit code.

The CI environment must be able to bootstrap your application and register the relevant scheduled events. This command does not inspect your live production server or verify that its scheduler is running.

## Exit codes

| Exit code | Meaning |
|---|---|
| `0` | No findings matched the current detection rule. |
| Non-zero | Potential issues were detected, or the command failed. |

## Limitations

This package does not:

- Execute scheduled tasks.
- Modify or automatically fix your schedule.
- Verify your server's cron configuration.
- Confirm that Laravel's scheduler is running in production.
- Inspect live processes or production logs.
- Detect every possible scheduling or concurrency issue.
- Guarantee that your scheduled tasks are safe.

A clean audit means only that no events matched the current detection rule.

## Troubleshooting

### The Artisan command is unavailable

Try:

```bash
composer dump-autoload
php artisan package:discover
php artisan schedule:audit --help
```

If the issue persists, check your package-discovery configuration and application bootstrap errors.

### No findings are reported

The current version checks only events with the exact cron expression `* * * * *` and without `withoutOverlapping()` protection. Other scheduling risks are outside its current scope.

## Compatibility

The package targets Laravel 12 and Laravel 13, subject to the PHP and dependency constraints declared in `composer.json`.

## Development

Clone the repository and install its dependencies:

```bash
composer install
```

Run the tests:

```bash
vendor/bin/phpunit
```

Validate Composer metadata:

```bash
composer validate --strict
```

Audit dependencies:

```bash
composer audit
```

## Contributing

Bug reports, reproducible examples, and pull requests are welcome.

Include your PHP version, Laravel version, package version, command output, and a minimal example when reporting an issue. Do not share secrets or sensitive production data.

## License

This package is licensed under the MIT license. See [LICENSE](https://github.com/asfiaaiman/Laravel-Schedule-Audit/blob/HEAD/LICENSE).
