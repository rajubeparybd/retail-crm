# Actions

Actions encapsulate business logic. Controllers delegate to Actions, staying thin.

## Creating Actions

```bash
php artisan make:class Actions/CreateUser
php artisan make:class Actions/ProcessPayment
```

## Action Structure

```php
class Actions\CreateUser
{
    public function __construct(
        private PasswordHasher $hasher,
        private EmailVerifier $verifier,
    ) {}

    public function execute(array $data): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $this->hasher->hash($data['password']),
        ]);

        $this->verifier->send($user);

        return $user;
    }
}
```

## Using Actions in Controllers

```php
class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, CreateUser $action)
    {
        $user = $action->execute($request->validated());

        return response()->json([
            'user' => new UserResource($user),
        ], 201);
    }
}
```

## Why Actions?

- **Testability**: Isolate business logic from HTTP layer
- **Reusability**: Use same Action from console, queues, events
- **Single responsibility**: Controller = HTTP, Action = business logic
- **Dependency injection**: Easier to mock dependencies

## Action Patterns

**Simple action**: Single public method, constructor injection
**Complex action**: Private methods for internal logic, can use value objects
**Validation**: Use Form Requests or separate validator classes

## Naming Convention

Use verb-noun pattern: `CreateUser`, `ProcessPayment`, `SendNotification`, `GenerateReport`
