<?php

declare(strict_types=1);

use App\Events\CustomerBecameLost;
use App\Mail\LostCustomerPromotionalMail;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('sends a promotional email when CustomerBecameLost is fired', function (): void {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'test@example.com']);

    CustomerBecameLost::dispatch($customer);

    Mail::assertQueued(LostCustomerPromotionalMail::class, fn (LostCustomerPromotionalMail $mail): bool => $mail->customer->is($customer));
});

it('queues the promotional email to the correct recipient', function (): void {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'jane.doe@example.com']);

    CustomerBecameLost::dispatch($customer);

    Mail::assertQueued(LostCustomerPromotionalMail::class, fn (LostCustomerPromotionalMail $mail): bool => $mail->hasTo($customer->email));
});

it('does not send an email to unrelated customers when the event fires', function (): void {
    Mail::fake();

    $targetCustomer = Customer::factory()->create(['email' => 'target@example.com']);
    $otherCustomer = Customer::factory()->create(['email' => 'other@example.com']);

    CustomerBecameLost::dispatch($targetCustomer);

    Mail::assertNotQueued(LostCustomerPromotionalMail::class, fn (LostCustomerPromotionalMail $mail): bool => $mail->hasTo($otherCustomer->email));
});
