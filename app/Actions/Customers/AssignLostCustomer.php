<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Events\LostCustomerAssigned;
use App\Models\Customer;
use App\Models\User;

class AssignLostCustomer
{
    public function execute(Customer $customer, ?User $employee): void
    {
        $customer->assigned_employee_id = $employee?->id;
        $customer->save();

        if ($employee instanceof User && $employee->hasRole('employee')) {
            LostCustomerAssigned::dispatch($customer, $employee);
        }
    }
}
