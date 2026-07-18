# Queues

Queues defer time-consuming tasks. Jobs in `app/Jobs/`. Use for emails, processing, API calls.

## Creating Jobs

```bash
php artisan make:job ProcessOrder
php artisan make:job SendWelcomeEmail --queued
```

## Job Structure

```php
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [30, 60, 120];
    public $queue = 'orders';

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        // Process order logic
    }

    public function failed(Throwable $exception): void
    {
        // Handle failure
    }
}
```

## Dispatching Jobs

```php
ProcessOrder::dispatch($order);
ProcessOrder::dispatch($order)->onQueue('high')->delay(now()->addMinutes(5));
ProcessOrder::dispatchIf($condition, $order);
ProcessOrder::dispatchUnless($condition, $order);
```

## Running Queue Workers

```bash
php artisan queue:work
php artisan queue:work --queue=high,default,low
php artisan queue:work --tries=3
```

## Failed Jobs

```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
```

## Database Transactions

Queued jobs within transactions may process before commit. Use `ShouldQueueAfterCommit` or configure `after_commit => true`.
