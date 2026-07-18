<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Ensure the admin and employee roles exist for every test.
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'employee']);
});

describe('authentication & authorization', function (): void {
    test('guests are redirected to login', function (): void {
        $this->get(route('customers.lost-customers.index'))
            ->assertRedirect(route('login'));
    });

    test('non-admin authenticated users get a 403', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.lost-customers.index'))
            ->assertForbidden();
    });

    test('admin users can access the lost-customers index', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('customers.lost-customers.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->component('customers/lost-customers/index')
                    ->has('customers')
                    ->has('employees')
                    ->has('days'),
            );
    });
});

describe('index', function (): void {
    test('it shows customers who have never purchased as lost', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Customer with no sales is always considered "lost".
        $customer = Customer::factory()->create(['name' => 'Lost Larry']);

        $this->actingAs($admin)
            ->get(route('customers.lost-customers.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->where('customers.data.0.name', 'Lost Larry')
                    ->where('customers.data.0.assigned_employee', null),
            );
    });

    test('it lists employees in the sidebar scoreboard', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create(['name' => 'Alice']);

        $this->actingAs($admin)
            ->get(route('customers.lost-customers.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page): AssertableInertia => $page
                    ->has('employees')
                    ->where('employees.0.kpi_score', 0),
            );
    });
});

describe('update (assignment)', function (): void {
    test('admin can assign an employee to a lost customer', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $employee = User::factory()->create();
        $customer = Customer::factory()->create();

        $this->actingAs($admin)
            ->put(route('customers.lost-customers.update', $customer), [
                'employee_id' => $employee->id,
            ])
            ->assertRedirect();

        expect($customer->fresh()->assigned_employee_id)->toBe($employee->id);
    });

    test('admin can unassign an employee by passing null', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $employee = User::factory()->create();
        $customer = Customer::factory()->create(['assigned_employee_id' => $employee->id]);

        $this->actingAs($admin)
            ->put(route('customers.lost-customers.update', $customer), [
                'employee_id' => null,
            ])
            ->assertRedirect();

        expect($customer->fresh()->assigned_employee_id)->toBeNull();
    });

    test('it validates employee_id must exist in users table', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $customer = Customer::factory()->create();

        $this->actingAs($admin)
            ->put(route('customers.lost-customers.update', $customer), [
                'employee_id' => 99999,
            ])
            ->assertSessionHasErrors('employee_id');
    });

    test('non-admin users cannot update assignments', function (): void {
        $user = User::factory()->create();
        $employee = User::factory()->create();
        $customer = Customer::factory()->create();

        $this->actingAs($user)
            ->put(route('customers.lost-customers.update', $customer), [
                'employee_id' => $employee->id,
            ])
            ->assertForbidden();
    });
});
