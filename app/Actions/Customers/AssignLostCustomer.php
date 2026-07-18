<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Events\LostCustomerAssigned;
use App\Models\Customer;
use App\Models\User;

class AssignLostCustomer
{
    /**
     * Assign (or clear) the follow-up employee for a lost customer.
     *
     * Passing null unassigns the current employee without firing the event.
     */
    public function execute(Customer $customer, ?User $employee): void
    {
        $customer->assigned_employee_id = $employee?->id;
        $customer->save();

        if ($employee !== null) {
            LostCustomerAssigned::dispatch($customer, $employee);
        }
    }
}
