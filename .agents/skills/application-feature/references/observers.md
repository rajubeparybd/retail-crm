# Observers

Observers group model event listeners into single class. Auto-registered with `#[ObservedBy]` attribute.

## Creating Observer

```bash
php artisan make:observer UserObserver --model=User
```

## Observer Structure

```php
class UserObserver
{
    public function created(User $user): void {}
    public function updated(User $user): void {}
    public function deleted(User $user): void {}
    public function restored(User $user): void {}
    public function forceDeleted(User $user): void {}
}
```

## Registering Observer

**Attribute (recommended)**:

```php
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable {}
```

## Additional Events

- `saving()` - before save
- `saved()` - after save
- `creating()` - before insert
- `updating()` - before update
- `deleting()` - before delete

## Use Cases

- Update cache when model changes
- Log model modifications
- Send notifications on model state changes
- Sync related models
- Calculate derived fields
