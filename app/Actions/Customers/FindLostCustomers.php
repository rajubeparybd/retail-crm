<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class FindLostCustomers
{
    /**
     * @return Collection<int, Customer>
     */
    public function execute(int $days): Collection
    {
        $threshold = now()->subDays($days);

        return Customer::whereDoesntHave('sales', function ($query) use ($threshold): void {
            $query->where('created_at', '>=', $threshold);
        })->get();
    }
}
