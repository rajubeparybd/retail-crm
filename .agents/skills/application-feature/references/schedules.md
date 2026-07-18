# Schedules

Define scheduled tasks in `app/Console/Kernel.php` with `schedule()` method.

## Creating Commands

```bash
php artisan make:command GenerateDailyReport
```

## Command Structure

```php
class GenerateDailyReport extends Command
{
    protected $signature = 'reports:generate {--date=}';
    protected $description = 'Generate daily reports';

    public function handle(): int
    {
        $date = $this->option('date') ?? now()->toDateString();
        // Generate report
        return self::SUCCESS;
    }
}
```

## Scheduling Tasks

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('reports:generate')->daily();
    $schedule->command('emails:send')->hourly();
    $schedule->command('cache:clear')->weekly();
    $schedule->command('backup:run')->dailyAt('02:00');
    $schedule->command('invoices:generate')->everyFiveMinutes();
}
```

## Schedule Options

- `->daily()`, `->hourly()`, `->weekly()`, `->monthly()`
- `->dailyAt('13:00')`
- `->everyMinute()`, `->everyFiveMinutes()`
- `->cron('* * * * *')` - custom cron expression
- `->weekdays()`, `->weekends()`, `->sundays()`
- `->between('09:00', '17:00')`
- `->when(fn () => true)` - conditional execution

## Running Scheduler

Add to crontab:

```
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```

Or run schedule worker:

```bash
php artisan schedule:work
```

## Managing Schedule

```bash
php artisan schedule:list
php artisan schedule:test "reports:generate"
php artisan schedule:run
```
