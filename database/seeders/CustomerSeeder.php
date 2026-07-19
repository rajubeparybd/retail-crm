<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = User::role('employee')->get();

        Customer::factory(50)->create()->each(function ($customer) use ($employees): void {
            if (random_int(0, 1) && $employees->count() > 0) {
                $customer->update(['assigned_employee_id' => $employees->random()->id]);
            }
        });
    }
}
