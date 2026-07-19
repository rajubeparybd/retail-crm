<?php

declare(strict_types=1);

use App\Actions\Sales\ProcessCheckout;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('authentication', function (): void {
    test('guests are redirected from the customers index', function (): void {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    });

    test('guests are redirected from a customer detail page', function (): void {
        $customer = Customer::factory()->create();

        $this->get(route('customers.show', $customer))->assertRedirect(route('login'));
    });
});

describe('index', function (): void {
    test('it lists customers with their aggregate purchase stats', function (): void {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['name' => 'Jane']);
        $product = Product::factory()->create([
            'price' => '10.00',
            'stock_quantity' => 100,
        ]);

        app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 2],
        ], $customer);

        app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 3],
        ], $customer);

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->has('customers.data', 1)
                    ->where('customers.total', 1)
                    ->where('customers.data.0.name', 'Jane')
                    ->where('customers.data.0.purchase_count', 2)
                    ->where('customers.data.0.total_spent', '50.00')
                    ->has('customers.data.0.last_purchase_at'),
            );
    });

    test('walk-in sales are not attributed to any customer', function (): void {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 1],
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('customers.data.0.purchase_count', 0)
                    ->where('customers.data.0.total_spent', null)
                    ->where('customers.data.0.last_purchase_at', null),
            );

        expect(Sale::where('customer_id', $customer->id)->exists())->toBeFalse();
    });

    test('customers are paginated ten per page', function (): void {
        Customer::factory()->count(12)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('customers.current_page', 2)
                    ->where('customers.last_page', 2)
                    ->where('customers.per_page', 10)
                    ->where('customers.total', 12),
            );
    });

    test('it filters customers by name, email, or phone', function (): void {
        Customer::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '555-0100',
        ]);
        Customer::factory()->create([
            'name' => 'Acme Corp',
            'email' => 'billing@acme.co',
            'phone' => '555-9900',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index', ['search' => 'jane']))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('search', 'jane')
                    ->where('customers.total', 1)
                    ->where('customers.data.0.name', 'Jane Doe'),
            );

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index', ['search' => 'acme.co']))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('customers.total', 1)
                    ->where('customers.data.0.name', 'Acme Corp'),
            );

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index', ['search' => '555-9900']))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('customers.total', 1)
                    ->where('customers.data.0.name', 'Acme Corp'),
            );
    });

    test('an empty search lists all customers', function (): void {
        Customer::factory()->count(3)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('customers.index', ['search' => '   ']))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('search', '')
                    ->where('customers.total', 3),
            );
    });
});

describe('show', function (): void {
    test('it shows 404 for a missing customer', function (): void {
        $this->actingAs(User::factory()->create())
            ->get(route('customers.show', 9999))
            ->assertNotFound();
    });

    test('it shows the customer stats and paginated purchases', function (): void {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['name' => 'Jane']);
        $product = Product::factory()->create([
            'price' => '10.00',
            'stock_quantity' => 100,
        ]);

        app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 1],
        ], $customer);

        app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 4],
        ], $customer);

        $this->actingAs(User::factory()->create())
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('customer.name', 'Jane')
                    ->where('stats.purchase_count', 2)
                    ->where('stats.total_spent', '50.00')
                    ->has('stats.last_purchase_at')
                    ->where('sales.total', 2)
                    ->has('sales.data', 2),
            );
    });

    test('the last purchase date is the exact max created_at', function (): void {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 100]);

        $older = app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 1],
        ], $customer);

        $newer = app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 1],
        ], $customer);

        $older->forceFill(['created_at' => now()->subDays(10)])->save();
        $newer->forceFill(['created_at' => now()->startOfSecond()])->save();

        $this->actingAs(User::factory()->create())
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('stats.last_purchase_at', $newer->created_at->toDateTimeString()),
            );
    });

    test('customers with no sales show zero stats and null last purchase', function (): void {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('stats.purchase_count', 0)
                    ->where('stats.total_spent', '0.00')
                    ->where('stats.first_purchase_at', null)
                    ->where('stats.last_purchase_at', null)
                    ->where('stats.avg_per_month', null)
                    ->where('sales.total', 0),
            );
    });

    test('purchases paginate ten per page', function (): void {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 1000]);

        foreach (range(1, 12) as $i) {
            app(ProcessCheckout::class)->execute($user, [
                ['product_id' => $product->id, 'quantity' => 1],
            ], $customer);
        }

        $this->actingAs(User::factory()->create())
            ->get(route('customers.show', ['customer' => $customer, 'page' => 2]))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('sales.current_page', 2)
                    ->where('sales.per_page', 10)
                    ->where('sales.total', 12),
            );
    });
});
