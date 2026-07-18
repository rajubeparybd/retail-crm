# Events & Listeners

Events provide observer pattern for decoupling. Events in `app/Events/`, listeners in `app/Listeners/`.

## Creating Events & Listeners

```bash
php artisan make:event OrderPlacedEvent
php artisan make:listener SendOrderNotificationListener --event=OrderPlacedEvent
```

Or create both at once in `EventServiceProvider`:

```php
protected $listen = [
    OrderPlacedEvent::class => [
        SendOrderNotificationListener::class,
        UpdateInventoryListener::class,
    ],
];
```

Then run: `php artisan event:list`

## Event Structure

```php
class OrderPlacedEvent
{
    use SerializesModels;

    public function __construct(public Order $order) {}
}
```

## Listener Structure

Sync listener:

```php
class SendOrderNotificationListener
{
    public function handle(OrderPlacedEvent $event): void
    {
        // Send notification
    }
}
```

Queued listener (implements `ShouldQueue`):

```php
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 30; // seconds

    public function handle(OrderPlaced $event): void {}
}
```

## Dispatching Events

```php
event(new OrderPlaced($order));
// or
OrderPlaced::dispatch($order);
```

## Use Cases

- Order placed → send notification, update inventory
- User registered → send welcome email, create profile
- Payment completed → generate invoice, update subscription
