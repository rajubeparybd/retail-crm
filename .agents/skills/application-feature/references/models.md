# Models

Models represent database tables. Use Eloquent ORM for data access.

## Creating Models

```bash
php artisan make:model Order
php artisan make:model Order --migration --factory --seed
php artisan make:model Order --api
```

## Model Structure

```php
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'total', 'status'];
    protected $casts = [
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    protected static function booted(): void
    {
        static::creating(fn ($order) => $order->status ??= 'pending');
    }
}
```

## Relationships

- `hasOne()`, `hasMany()`, `belongsTo()`, `belongsToMany()`
- `hasManyThrough()`, `morphOne()`, `morphMany()`, `morphToMany()`

## Scopes

```php
$orders = Order::completed()->latest()->get();
```

## Accessors & Mutators

```php
protected function formattedTotal(): Attribute
{
    return Attribute::make(
        get: fn ($value) => '$' . number_format($this->total, 2),
    );
}
```

## Inspection

```bash
php artisan model:show Order
php artisan model:prune
```
