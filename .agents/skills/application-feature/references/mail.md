# Mail

Mailables encapsulate email logic in `app/Mail/`.

## Creating Mailables

```bash
php artisan make:mail OrderConfirmation
php artisan make:mail WelcomeEmail --markdown=emails.welcome
```

## Mailable Structure

```php
class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation',
            from: new Address('noreply@example.com', 'Example Store'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: ['order' => $this->order],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

## Sending Mail

```php
Mail::to($user->email)->send(new OrderConfirmation($order));
Mail::to($user->email)->cc($manager->email)->bcc($admin->email)->send(...);
```

## Queueing Mail

```php
Mail::to($user->email)->queue(new WelcomeEmail($user));
Mail::to($user->email)->later(now()->addMinutes(10), new WelcomeEmail($user));
```

## Markdown Mailables

Use Markdown for quick email templates:

```bash
php artisan make:mail WelcomeEmail --markdown=emails.welcome
```

Components available: `@component('mail::button')`, `@component('mail::table')`, etc.

## Testing

```php
Mail::fake();
Mail::assertSent(OrderConfirmation::class, fn ($mail) => $mail->order->id === $orderId);
```
