<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique(Product::class)],
            'price' => ['required', 'numeric', 'min:0', 'decimal:2'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
