<?php

declare(strict_types=1);

use App\Actions\Crm\IncrementEmployeeKpi;
use App\Events\LostCustomerMadePurchase;
use App\Listeners\IncrementKpiOnPurchaseListener;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'admin']);
});

describe('IncrementEmployeeKpi action', function (): void {
    test('it atomically increments the employee kpi_score by 1 by default', function (): void {
        $employee = User::factory()->create(['kpi_score' => 5]);

        app(IncrementEmployeeKpi::class)->execute($employee);

        expect($employee->fresh()->kpi_score)->toBe(6);
    });

    test('it increments by a custom amount', function (): void {
        $employee = User::factory()->create(['kpi_score' => 0]);

        app(IncrementEmployeeKpi::class)->execute($employee, 10);

        expect($employee->fresh()->kpi_score)->toBe(10);
    });
});

describe('KPI event-driven increment via sale', function (): void {
    test('completing a sale for a customer with an assigned employee fires the event', function (): void {
        Event::fake([LostCustomerMadePurchase::class]);

        $employee = User::factory()->create();
        $customer = Customer::factory()->create(['assigned_employee_id' => $employee->id]);
        $product = Product::factory()->create(['price' => '10.00', 'stock_quantity' => 50]);
        $cashier = User::factory()->create();

        $this->actingAs($cashier)->post(route('sales.store'), [
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        Event::assertDispatched(fn (LostCustomerMadePurchase $e): bool => $e->customer->id === $customer->id
            && $e->employee->id === $employee->id);
    });

    test('completing a sale for a customer without an assigned employee does not fire the event', function (): void {
        Event::fake([LostCustomerMadePurchase::class]);

        $customer = Customer::factory()->create(['assigned_employee_id' => null]);
        $product = Product::factory()->create(['price' => '10.00', 'stock_quantity' => 50]);
        $cashier = User::factory()->create();

        $this->actingAs($cashier)->post(route('sales.store'), [
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        Event::assertNotDispatched(LostCustomerMadePurchase::class);
    });

    test('the IncrementKpiOnPurchaseListener increments the employee kpi_score', function (): void {
        $employee = User::factory()->create(['kpi_score' => 3]);
        $customer = Customer::factory()->create(['assigned_employee_id' => $employee->id]);

        $listener = new IncrementKpiOnPurchaseListener();
        $listener->handle(new LostCustomerMadePurchase($customer, $employee));

        expect($employee->fresh()->kpi_score)->toBe(4);
    });
});
