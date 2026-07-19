<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = User::role('employee')->get();
        $customers = Customer::all();
        $products = Product::all();

        if ($employees->isEmpty() || $products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 30; $i++) {
            $sale = Sale::create([
                'user_id' => $employees->random()->id,
                'customer_id' => $customers->isNotEmpty() && random_int(0, 1) ? $customers->random()->id : null,
                'total' => 0,
            ]);

            $itemCount = random_int(1, 5);
            $total = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = random_int(1, 4);
                $unitPrice = $product->price;
                $subtotal = bcmul((string) $unitPrice, (string) $quantity, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $total = bcadd((string) $total, $subtotal, 2);
            }

            $sale->update(['total' => $total]);
        }
    }
}
