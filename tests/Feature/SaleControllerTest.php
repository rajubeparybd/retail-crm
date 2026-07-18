<?php

declare(strict_types=1);

use App\Actions\Sales\ProcessCheckout;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('authentication', function (): void {
    test('guests are redirected from the sales index', function (): void {
        $this->get(route('sales.index'))->assertRedirect(route('login'));
    });
});

describe('index', function (): void {
    test('authenticated users see the list of sales', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => '10.00',
            'stock_quantity' => 10,
        ]);

        app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 2],
        ]);

        $this->actingAs($user)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->has('sales.data', 1)
                    ->where('sales.total', 1)
                    ->where('sales.data.0.total', '20.00'),
            );
    });

    test('sales are paginated ten per page', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 1000]);

        foreach (range(1, 12) as $i) {
            app(ProcessCheckout::class)->execute($user, [
                ['product_id' => $product->id, 'quantity' => 1],
            ]);
        }

        $this->actingAs(User::factory()->create())
            ->get(route('sales.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('sales.current_page', 2)
                    ->where('sales.last_page', 2)
                    ->where('sales.per_page', 10)
                    ->where('sales.total', 12),
            );
    });
});

describe('checkout', function (): void {
    test('guests are redirected from the checkout page', function (): void {
        $this->get(route('sales.create'))->assertRedirect(route('login'));
    });

    test('the checkout page lists products', function (): void {
        $products = Product::factory()->count(2)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('sales.create'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page->has('products', 2),
            );
    });
});

describe('store', function (): void {
    test('a purchase records the customer, salesman, and sale', function (): void {
        $salesman = User::factory()->create();
        $product = Product::factory()->create([
            'price' => '10.00',
            'stock_quantity' => 5,
        ]);

        $this->actingAs($salesman)
            ->post(route('sales.store'), [
                'customer_email' => 'jane@example.com',
                'customer_name' => 'Jane Buyer',
                'customer_phone' => '555-0100',
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ])
            ->assertRedirect(route('sales.index'));

        expect(Product::find($product->id)->stock_quantity)->toBe(3);

        $customer = Customer::where('email', 'jane@example.com')->first();
        expect($customer)->not->toBeNull()
            ->name->toBe('Jane Buyer');

        $this->assertDatabaseHas('sales', [
            'user_id' => $salesman->id,
            'customer_id' => $customer->id,
            'total' => '20.00',
        ]);
    });

    test('reusing an existing customer email does not duplicate the customer', function (): void {
        $existing = Customer::factory()->create([
            'email' => 'dup@example.com',
            'name' => 'Original',
        ]);
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->actingAs(User::factory()->create())
            ->post(route('sales.store'), [
                'customer_email' => 'dup@example.com',
                'customer_name' => 'Changed Name',
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertRedirect(route('sales.index'));

        expect(Customer::where('email', 'dup@example.com')->count())->toBe(1)
            ->and(Customer::find($existing->id)->name)->toBe('Original');
    });

    test('a purchase with insufficient stock is rejected and nothing is recorded', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 1]);

        $this->actingAs($user)
            ->from(route('sales.create'))
            ->post(route('sales.store'), [
                'customer_email' => 'nope@example.com',
                'customer_name' => 'No Sale',
                'items' => [['product_id' => $product->id, 'quantity' => 5]],
            ])
            ->assertRedirect(route('sales.create'));

        expect(Product::find($product->id)->stock_quantity)->toBe(1);
        $this->assertDatabaseMissing('sales', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('customers', ['email' => 'nope@example.com']);
    });

    test('store requires customer email and name', function (): void {
        $this->actingAs(User::factory()->create())
            ->post(route('sales.store'), [
                'customer_email' => '',
                'customer_name' => '',
                'items' => [['product_id' => Product::factory()->create()->id, 'quantity' => 1]],
            ])
            ->assertSessionHasErrors(['customer_email', 'customer_name']);
    });

    test('store requires at least one item', function (): void {
        $this->actingAs(User::factory()->create())
            ->post(route('sales.store'), [
                'customer_email' => 'x@example.com',
                'customer_name' => 'X',
                'items' => [],
            ])
            ->assertSessionHasErrors('items');
    });

    test('store validates quantity is at least one', function (): void {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('sales.store'), [
                'customer_email' => 'x@example.com',
                'customer_name' => 'X',
                'items' => [['product_id' => $product->id, 'quantity' => 0]],
            ])
            ->assertSessionHasErrors('items.0.quantity');
    });
});

describe('findCustomer', function (): void {
    test('returns the customer matching the email', function (): void {
        $customer = Customer::factory()->create([
            'email' => 'match@example.com',
            'name' => 'Match',
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('sales.customer', ['email' => 'match@example.com']))
            ->assertOk()
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('customer.name', 'Match');
    });

    test('returns a null customer when the email is unknown', function (): void {
        $this->actingAs(User::factory()->create())
            ->getJson(route('sales.customer', ['email' => 'ghost@example.com']))
            ->assertOk()
            ->assertJsonPath('customer', null);
    });

    test('guests are unauthorized on the customer lookup', function (): void {
        $this->getJson(route('sales.customer', ['email' => 'a@b.com']))
            ->assertUnauthorized();
    });
});
