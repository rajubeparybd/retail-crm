<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = \App\Models\User::role('employee')->get();

        \App\Models\Customer::factory(50)->create()->each(function ($customer) use ($employees) {
            if (rand(0, 1) && $employees->count() > 0) {
                $customer->update(['assigned_employee_id' => $employees->random()->id]);
            }
        });
    }
}
