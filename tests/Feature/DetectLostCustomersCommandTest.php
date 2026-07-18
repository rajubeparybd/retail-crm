<?php

declare(strict_types=1);

use App\Jobs\NotifyLostCustomerJob;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Bus;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('dispatches jobs for customers whose last purchase was before the threshold', function (): void {
    Bus::fake();

    $lostCustomer = Customer::factory()->create();
    Sale::factory()->for($lostCustomer, 'customer')->create([
        'created_at' => now()->subDays(95),
    ]);

    $activeCustomer = Customer::factory()->create();
    Sale::factory()->for($activeCustomer, 'customer')->create([
        'created_at' => now()->subDays(10),
    ]);

    $this->artisan('crm:detect-lost-customers', ['--days' => 90])
        ->assertSuccessful();

    Bus::assertDispatched(fn (NotifyLostCustomerJob $job): bool => $job->customer->is($lostCustomer));

    Bus::assertNotDispatched(NotifyLostCustomerJob::class, fn (NotifyLostCustomerJob $job): bool => $job->customer->is($activeCustomer));
});

it('dispatches a job for customers who have never purchased', function (): void {
    Bus::fake();

    $neverBoughtCustomer = Customer::factory()->create();

    $this->artisan('crm:detect-lost-customers', ['--days' => 90])
        ->assertSuccessful();

    Bus::assertDispatched(fn (NotifyLostCustomerJob $job): bool => $job->customer->is($neverBoughtCustomer));
});

it('uses the --days option to override the config threshold', function (): void {
    Bus::fake();

    $customer = Customer::factory()->create();
    Sale::factory()->for($customer, 'customer')->create([
        'created_at' => now()->subDays(40),
    ]);

    $this->artisan('crm:detect-lost-customers', ['--days' => 90])->assertSuccessful();
    Bus::assertNothingDispatched();

    $this->artisan('crm:detect-lost-customers', ['--days' => 30])->assertSuccessful();
    Bus::assertDispatched(NotifyLostCustomerJob::class);
});

it('outputs a success message when no lost customers exist', function (): void {
    Bus::fake();

    $active = Customer::factory()->create();
    Sale::factory()->for($active, 'customer')->create([
        'created_at' => now()->subDays(5),
    ]);

    $this->artisan('crm:detect-lost-customers', ['--days' => 90])
        ->assertSuccessful()
        ->expectsOutputToContain('No lost customers found');

    Bus::assertNothingDispatched();
});
