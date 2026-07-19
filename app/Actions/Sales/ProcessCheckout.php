<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Events\PurchaseSuccessful;
use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessCheckout
{
    public function execute(User $salesman, array $items, ?Customer $customer = null): Sale
    {
        return DB::transaction(function () use ($salesman, $items, $customer): Sale {
            $this->ensureValidItems($items);

            $productIds = collect($items)->pluck('product_id')->unique();

            /** @var Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = '0';
            $saleItems = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);

                if ($product === null) {
                    throw new InvalidArgumentException("Product [{$item['product_id']}] does not exist.");
                }

                $quantity = (int) $item['quantity'];

                if ($product->stock_quantity < $quantity) {
                    throw new InsufficientStockException(
                        $product,
                        $quantity,
                        (int) $product->stock_quantity
                    );
                }

                $subtotal = bcmul((string) $product->price, (string) $quantity, 2);
                $total = bcadd($total, $subtotal, 2);

                $saleItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                $product->stock_quantity -= $quantity;
            }

            $sale = $salesman->sales()->make(['total' => $total]);

            if ($customer instanceof Customer) {
                $sale->customer()->associate($customer);
            }

            $sale->save();
            $sale->items()->createMany($saleItems);

            foreach ($products as $product) {
                if ($product->isDirty('stock_quantity')) {
                    $product->save();
                }
            }

            $sale->load('items', 'customer');

            event(new PurchaseSuccessful($sale));

            return $sale;
        });
    }

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
