<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FindLostCustomers
{
    /**
     * @return Collection<int, Customer>
     */
    public function execute(int $days): Collection
    {
        $threshold = now()->subDays($days);

        return Customer::query()
            ->where(function ($query) use ($threshold): void {
                $query->whereExists(function ($sub) use ($threshold): void {
                    $sub->select(DB::raw(1))
                        ->from('sales')
                        ->whereColumn('sales.customer_id', 'customers.id')
                        ->havingRaw('MAX(sales.created_at) < ?', [$threshold->toDateTimeString()])
                        ->groupBy('sales.customer_id');
                })
                    ->orWhereNotExists(function ($sub): void {
                        $sub->select(DB::raw(1))
                            ->from('sales')
                            ->whereColumn('sales.customer_id', 'customers.id');
                    });
            })
            ->get();
    }
}
