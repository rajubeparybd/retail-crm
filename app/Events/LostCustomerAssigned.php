<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LostCustomerAssigned
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Customer $customer,
        public readonly User $employee,
    ) {}
}
