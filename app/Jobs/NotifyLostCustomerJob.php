<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\CustomerBecameLost;
use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;

#[Tries(3)]
#[Backoff(60)]
class NotifyLostCustomerJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Customer $customer) {}

    public function handle(): void
    {
        CustomerBecameLost::dispatch($this->customer);
    }
}
