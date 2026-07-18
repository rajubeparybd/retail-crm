<?php

declare(strict_types=1);

use App\Actions\Sales\ProcessCheckout;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function (): void {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
});

test('the dashboard shows sales stats and recent sales', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => '10.00',
        'stock_quantity' => 20,
    ]);

    app(ProcessCheckout::class)->execute($user, [
        ['product_id' => $product->id, 'quantity' => 2],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('stats.today_revenue')
                ->where('stats.total_sales', 1)
                ->where('stats.low_stock_count', 0)
                ->has('recentSales', 1)
                ->where('recentSales.0.total', '20.00')
                ->where('recentSales.0.user.name', $user->name)
                ->has('recentSales.0.items', 1),
        );
});
