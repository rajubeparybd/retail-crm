<?php

declare(strict_types=1);

use App\Actions\Sales\ProcessCheckout;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

describe('successful checkout', function (): void {
    test('it records a sale and deducts stock for each line', function (): void {
        $productA = Product::factory()->create(['price' => '10.00', 'stock_quantity' => 10]);
        $productB = Product::factory()->create(['price' => '5.50', 'stock_quantity' => 8]);

        $sale = app(ProcessCheckout::class)->execute(User::factory()->create(), [
            ['product_id' => $productA->id, 'quantity' => 2],
            ['product_id' => $productB->id, 'quantity' => 3],
        ]);

        expect($sale)->toBeInstanceOf(Sale::class)
            ->and($sale->total)->toBe('36.50')
            ->and($sale->items)->toHaveCount(2)
            ->and($productA->fresh()->stock_quantity)->toBe(8)
            ->and($productB->fresh()->stock_quantity)->toBe(5);
    });

    test('it attaches the authenticated user as the sale owner', function (): void {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $sale = app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $product->id, 'quantity' => 1],
        ]);

        expect($sale->user_id)->toBe($user->id);
    });

    test('it snapshots the unit price at sale time', function (): void {
        $product = Product::factory()->create(['price' => '12.00', 'stock_quantity' => 5]);

        $sale = app(ProcessCheckout::class)->execute(User::factory()->create(), [
            ['product_id' => $product->id, 'quantity' => 1],
        ]);

        $product->update(['price' => '99.99']);

        expect(SaleItem::where('sale_id', $sale->id)->value('unit_price'))->toBe('12.00');
    });
});

describe('insufficient stock', function (): void {
    test('it throws and leaves stock untouched when a line is short', function (): void {
        $user = User::factory()->create();
        $productA = Product::factory()->create(['stock_quantity' => 10]);
        $productB = Product::factory()->create(['stock_quantity' => 1]);

        expect(fn () => app(ProcessCheckout::class)->execute($user, [
            ['product_id' => $productA->id, 'quantity' => 2],
            ['product_id' => $productB->id, 'quantity' => 5],
        ]))->toThrow(InsufficientStockException::class);

        // Rollback: no sale recorded, both products' stock untouched.
        $this->assertDatabaseMissing('sales', ['user_id' => $user->id]);

        expect($productA->fresh()->stock_quantity)->toBe(10)
            ->and($productB->fresh()->stock_quantity)->toBe(1);
    });

    test('the exception carries the product and quantities', function (): void {
        $product = Product::factory()->create(['sku' => 'ABC', 'stock_quantity' => 2]);

        try {
            app(ProcessCheckout::class)->execute(User::factory()->create(), [
                ['product_id' => $product->id, 'quantity' => 5],
            ]);
        } catch (InsufficientStockException $e) {
            expect($e->product->is($product))->toBeTrue()
                ->and($e->requested)->toBe(5)
                ->and($e->available)->toBe(2);
        }

        expect($product->fresh()->stock_quantity)->toBe(2);
    });
});

describe('invalid input', function (): void {
    test('it throws when a product does not exist', function (): void {
        expect(fn () => app(ProcessCheckout::class)->execute(User::factory()->create(), [
            ['product_id' => 999999, 'quantity' => 1],
        ]))->toThrow(InvalidArgumentException::class);

        $this->assertDatabaseMissing('sales');
    });

    test('it rejects a zero quantity line', function (): void {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        expect(fn () => app(ProcessCheckout::class)->execute(User::factory()->create(), [
            ['product_id' => $product->id, 'quantity' => 0],
        ]))->toThrow(InvalidArgumentException::class);
    });

    test('it rejects an empty checkout', function (): void {
        expect(fn () => app(ProcessCheckout::class)->execute(User::factory()->create(), []))
            ->toThrow(InvalidArgumentException::class);
    });
});
