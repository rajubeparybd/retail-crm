<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function validProductData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Widget',
        'sku' => 'WID-001',
        'price' => '19.99',
        'stock_quantity' => 42,
    ], $overrides);
}

describe('authentication', function (): void {
    test('guests are redirected from the products index', function (): void {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    });

    test('guests cannot create a product', function (): void {
        $this->post(route('products.store'), validProductData())
            ->assertRedirect(route('login'));
    });
});

describe('index', function (): void {
    test('authenticated users see the list of products', function (): void {
        $user = User::factory()->create();
        $products = Product::factory()->count(2)->create();

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee($products[0]->name)
            ->assertSee($products[1]->name);
    });

    test('products are paginated ten per page', function (): void {
        Product::factory()->count(12)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page->has(
                    'products',
                    fn (AssertableInertia $products) => $products
                        ->where('current_page', 2)
                        ->where('last_page', 2)
                        ->where('per_page', 10)
                        ->where('total', 12)
                        ->has('data')
                        ->etc(),
                ),
            );
    });
});

describe('create', function (): void {
    test('authenticated users can open the create form', function (): void {
        $this->actingAs(User::factory()->create())
            ->get(route('products.create'))
            ->assertOk();
    });
});

describe('store', function (): void {
    test('authenticated users can create a product', function (): void {
        $this->actingAs(User::factory()->create())
            ->post(route('products.store'), validProductData())
            ->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'WID-001')->first();
        expect($product)->not->toBeNull()
            ->name->toBe('Widget')
            ->stock_quantity->toBe(42);
    });

    test('it validates required fields', function (string $field): void {
        $this->actingAs(User::factory()->create())
            ->post(route('products.store'), validProductData([$field => '']))
            ->assertSessionHasErrors($field);
    })->with(['name', 'sku', 'price', 'stock_quantity']);

    test('sku must be unique', function (): void {
        Product::factory()->create(['sku' => 'TAKEN']);

        $this->actingAs(User::factory()->create())
            ->post(route('products.store'), validProductData(['sku' => 'TAKEN']))
            ->assertSessionHasErrors('sku');
    });

    test('stock quantity cannot be negative', function (): void {
        $this->actingAs(User::factory()->create())
            ->post(route('products.store'), validProductData(['stock_quantity' => -1]))
            ->assertSessionHasErrors('stock_quantity');
    });
});

describe('update', function (): void {
    test('authenticated users can update a product', function (): void {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('products.update', $product), validProductData(['name' => 'Updated']))
            ->assertRedirect(route('products.index'));

        expect($product->fresh()->name)->toBe('Updated');
    });

    test('sku uniqueness ignores the current product', function (): void {
        $product = Product::factory()->create(['sku' => 'SAME']);

        $this->actingAs(User::factory()->create())
            ->patch(route('products.update', $product), validProductData(['sku' => 'SAME']))
            ->assertRedirect(route('products.index'));
    });

    test('another products sku cannot be reused', function (): void {
        Product::factory()->create(['sku' => 'TAKEN']);
        $product = Product::factory()->create(['sku' => 'ORIGINAL']);

        $this->actingAs(User::factory()->create())
            ->patch(route('products.update', $product), validProductData(['sku' => 'TAKEN']))
            ->assertSessionHasErrors('sku');
    });
});

describe('destroy', function (): void {
    test('authenticated users can delete a product', function (): void {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    });
});
