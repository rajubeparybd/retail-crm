<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

it('requires authentication', function (): void {
    $response = $this->getJson(route('api.products.index'));

    $response->assertStatus(401);
});

it('returns a paginated list of products with the correct structure', function (): void {
    $user = User::factory()->create();

    $product = Product::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.products.index'));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json->has('data')
            ->has('data.0', fn (AssertableJson $json) => $json->hasAll(['sku', 'product_name', 'price', 'available_stock'])
            )
            ->has('links')
            ->has('meta')
        );
});
