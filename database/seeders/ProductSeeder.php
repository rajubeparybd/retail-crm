<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->products() as $attributes) {
            Product::create($attributes);
        }
    }

    /**
     * @return array<int, array{name: string, sku: string, price: string, stock_quantity: int}>
     */
    private function products(): array
    {
        return [
            ['name' => 'Coca-Cola 500ml', 'sku' => 'BEV-001', 'price' => '1.50', 'stock_quantity' => 120],
            ['name' => 'Bottled Water 1L', 'sku' => 'BEV-002', 'price' => '0.99', 'stock_quantity' => 200],
            ['name' => 'Lays Classic 150g', 'sku' => 'SNK-001', 'price' => '2.75', 'stock_quantity' => 80],
            ['name' => 'Cadbury Dairy Milk', 'sku' => 'SNK-002', 'price' => '3.20', 'stock_quantity' => 60],
            ['name' => 'Instant Noodles', 'sku' => 'GRC-001', 'price' => '0.85', 'stock_quantity' => 3],
            ['name' => 'Basmati Rice 5kg', 'sku' => 'GRC-002', 'price' => '12.40', 'stock_quantity' => 40],
            ['name' => 'Toothpaste 100ml', 'sku' => 'HHL-001', 'price' => '4.10', 'stock_quantity' => 2],
            ['name' => 'Hand Soap Bar', 'sku' => 'HHL-002', 'price' => '1.20', 'stock_quantity' => 150],
        ];
    }
}
