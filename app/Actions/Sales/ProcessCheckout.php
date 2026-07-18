<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Records a sale for a multi-line cart and strictly controls inventory.
 *
 * Each product's stock is decremented atomically inside a single database
 * transaction. Pessimistic row locks (`lockForUpdate()`) prevent two
 * concurrent checkouts from both passing the stock check and overselling.
 * If any line is missing or has insufficient stock, an exception propagates
 * out of the transaction and the entire sale — including any stock already
 * deducted — is rolled back.
 */
class ProcessCheckout
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  Customer|null  $customer  buyer the POS operator attached (logged-in user is the salesman)
     */
    public function execute(User $salesman, array $items, ?Customer $customer = null): Sale
    {
        return DB::transaction(function () use ($salesman, $items, $customer): Sale {
            $this->ensureValidItems($items);

            $productIds = array_map(
                fn (array $item): int => (int) $item['product_id'],
                $items,
            );

            /** @var Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate BEFORE any write: every product exists with enough stock.
            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);

                if ($product === null) {
                    throw new InvalidArgumentException(
                        "Product [{$item['product_id']}] does not exist.",
                    );
                }

                $quantity = (int) $item['quantity'];

                if ($product->stock_quantity < $quantity) {
                    throw new InsufficientStockException(
                        $product,
                        $quantity,
                        (int) $product->stock_quantity,
                    );
                }
            }

            /** @var Sale $sale */
            $sale = $salesman->sales()->create(['total' => '0']);

            if ($customer instanceof \App\Models\Customer) {
                $sale->customer()->associate($customer);
            }

            $total = '0';
            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];

                $subtotal = bcmul((string) $product->price, (string) $quantity, 2);
                $total = bcadd($total, $subtotal, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock_quantity', $quantity);
            }

            $sale->forceFill(['total' => $total])->save();

            return $sale->load('items');
        });
    }

    /**
     * Guard the cart shape before touching the database.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    private function ensureValidItems(array $items): void
    {
        if ($items === []) {
            throw new InvalidArgumentException('Cannot process an empty checkout.');
        }

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? 0;

            if ($productId === null || $quantity <= 0) {
                throw new InvalidArgumentException(
                    'Each checkout line needs a valid product_id and a quantity of at least 1.',
                );
            }
        }
    }
}
