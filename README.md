# Laravel Schedule Audit

A lightweight Laravel package that identifies a common scheduling risk: tasks configured to run every minute without overlap protection.

Designed for Laravel developers who want a simple command-line check for potentially risky scheduled tasks.

## Features

- Detects scheduled tasks running every minute without `withoutOverlapping()` protection.
- Displays findings in a readable command-line table.
- Returns a non-zero exit code when potential issues are detected.
- Integrates with Laravel's package auto-discovery.
- Makes no automatic changes to your application's schedule.

## Requirements

- PHP 8.2 or later.
- Laravel 12 or Laravel 13.
- Composer.
- Git.

## Installation

**This package is currently distributed through GitHub and is not yet published on Packagist.**

You can install it directly from the GitHub repository using Composer.

### Step 1: Add the GitHub repository

Open your Laravel application's `composer.json`.

Add the following entry to its existing `repositories` array:

```json
{
    "type": "vcs",
    "url": "https://github.com/asfiaaiman/laravel-schedule-audit"
}
```

For example, if your application does not already have a `repositories` section, your configuration will look like this:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/asfiaaiman/laravel-schedule-audit"
        }
    ]
}
```

**Important:** If your application already has a `repositories` section, add the new entry to that array. Do not replace your existing repositories.

### Step 2: Install the package

If your repository's default branch is `main`, run:

```bash
composer require --dev asfiaaiman/laravel-schedule-audit:dev-main
```

The `--dev` flag installs the package as a development dependency, which is suitable when using the audit as a development or CI quality check.

If you want to run the audit as part of a production deployment, install it without `--dev` instead:

```bash
composer require asfiaaiman/laravel-schedule-audit:dev-main
```

Use the installation mode that matches your deployment process. A development dependency will not be available after a production install that excludes development dependencies.

**Note:** `dev-main` tracks the `main` branch, so the installed code can change as new commits are pushed. For production use, prefer a tagged release once version tags are available.

### Step 3: Verify installation

Run:

```bash
php artisan schedule:audit --help
```

Laravel should automatically discover the package's service provider. You do not need to register it manually.

If the command is unavailable, try:

```bash
composer dump-autoload
php artisan package:discover
```

Then run the help command again.

## Usage

Run the audit from the root directory of your Laravel application:

```bash
php artisan schedule:audit
```

The command inspects scheduled events registered with Laravel and reports events that match the package's current detection rule.

### Example: No findings

```text
No audit findings.
```

This means no scheduled events matched the current detection rule. It does not guarantee that every scheduled task is safe or correctly configured.

### Example: Potential issue detected

Illustrative output:

```text
+-----------------+----------------------+----------------------------------------------+
| Schedule        | Task                 | Finding                                      |
+-----------------+----------------------+----------------------------------------------+
| * * * * *       | reports:generate     | Runs every minute without overlap protection |
+-----------------+----------------------+----------------------------------------------+

1 potential issue(s) found.
```

The actual task name and results depend on the schedule registered in your application.

When findings are detected, the command returns a non-zero exit code. This makes it useful in scripts and CI pipelines.

## What does it check?

The current implementation detects scheduled events that meet both conditions:

1. Their cron expression is exactly `* * * * *`, meaning every minute.
2. They do not have Laravel's `withoutOverlapping()` protection enabled.

For example, consider this task in your application's scheduler:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:generate')
    ->everyMinute();
```

If the command takes longer than expected, concurrent executions may create duplicated work or resource contention, depending on the workload and execution environment.

To prevent overlapping executions when appropriate, you can use:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:generate')
    ->everyMinute()
    ->withoutOverlapping();
```

Laravel uses a cache-based mutex for overlap protection. Ensure that your cache and scheduling setup support the locking behavior your application requires.

Do not add `withoutOverlapping()` blindly to every task. Review whether concurrent executions are actually unsafe and whether another concurrency-control mechanism is already in place.

See the [Laravel Task Scheduling documentation](https://laravel.com/docs/12.x/scheduling) for more information.

## Using it in real projects

### Local development

Run the command after modifying scheduled tasks:

```bash
php artisan schedule:audit
```

Review any findings and decide whether overlap protection is appropriate for the affected tasks.

### Before deployment

Add the audit to your deployment checks:

```bash
php artisan schedule:audit
```

If the command returns a non-zero exit code, investigate the findings before proceeding.

The package does not modify your scheduler or automatically fix detected issues.

### GitHub Actions

You can run the audit in a GitHub Actions workflow after installing Composer dependencies and bootstrapping your Laravel application:

```yaml
- name: Audit scheduled tasks
  run: php artisan schedule:audit
```

If findings are detected, the command exits unsuccessfully and the workflow step fails.

The CI environment must be able to bootstrap the application and load the relevant schedule configuration. The audit evaluates registered scheduled events; it does not connect to your production server or verify that its scheduler is running.

## Exit codes

| Exit code | Meaning |
|---|---|
| `0` | No findings matched the current detection rule. |
| Non-zero | Potential issues were detected, or the command failed. |

Use the exit code to automate checks, but investigate failures rather than assuming every finding is a confirmed production defect.

## What this package does not do

The current version is intentionally limited. It does not:

- Execute scheduled tasks.
- Modify scheduler configuration.
- Automatically fix findings.
- Verify that your server's cron entry is configured correctly.
- Confirm that the Laravel scheduler is running in production.
- Inspect live processes or production logs.
- Detect every possible scheduling or concurrency issue.
- Guarantee that your scheduled tasks are safe.

A clean audit means only that the current detection rule found no matching events.

## Troubleshooting

### Composer cannot find the package

Verify that:

- The GitHub repository is public, or your Git credentials allow access.
- The repository URL is correct.
- The package's `composer.json` contains the package name `asfiaaiman/laravel-schedule-audit`.
- The branch name is correct. This README assumes the default branch is `main`.
- The package's PHP and Laravel constraints are compatible with your application.

Then retry:

```bash
composer update asfiaaiman/laravel-schedule-audit
```

### The Artisan command is unavailable

Run:

```bash
composer dump-autoload
php artisan package:discover
php artisan schedule:audit --help
```

If the issue persists, check your application's package-discovery configuration and bootstrap errors.

### No findings are reported

The current version only checks events with the exact cron expression `* * * * *` and without Laravel's `withoutOverlapping()` protection. It does not detect every scheduling risk.

## Compatibility

The package's Composer constraints target Laravel 12 and Laravel 13. PHP requirements also depend on the selected Laravel version.

Verify your application's compatibility before installing the package.

## Development

To run the package tests from a clone of this repository:

```bash
composer install
vendor/bin/phpunit
```

Validate Composer metadata:

```bash
composer validate --strict
```

Check installed dependency advisories:

```bash
composer audit
```

## Contributing

Bug reports, reproducible examples, and pull requests are welcome.

When reporting an issue, include your PHP version, Laravel version, package version, command output, and a minimal example of the relevant scheduled task.

Do not include credentials, secrets, or sensitive production data.

## License

This package is open-source software licensed under the MIT license. See [LICENSE](LICENSE).
