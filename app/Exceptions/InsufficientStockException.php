<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "Insufficient stock for product [{$product->sku}]: requested {$requested}, available {$available}.",
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'requested' => $this->requested,
            'available' => $this->available,
        ];
    }
}
