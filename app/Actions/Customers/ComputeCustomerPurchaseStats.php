<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;

class ComputeCustomerPurchaseStats
{
    public function execute(Customer $customer): array
    {
        $agg = Sale::whereCustomerId($customer->id)
            ->selectRaw(
                'COUNT(*) AS purchase_count, '
                . 'COALESCE(SUM(total), 0) AS total_spent, '
                . 'MIN(created_at) AS first_purchase_at, '
                . 'MAX(created_at) AS last_purchase_at',
            )
            ->toBase()
            ->first();

        $purchaseCount = (int) $agg->purchase_count;
        $firstPurchaseAt = $agg->first_purchase_at;

        return [
            'purchase_count' => $purchaseCount,
            'total_spent' => number_format((float) $agg->total_spent, 2, '.', ''),
            'first_purchase_at' => $firstPurchaseAt !== null ? (string) $firstPurchaseAt : null,
            'last_purchase_at' => $agg->last_purchase_at !== null ? (string) $agg->last_purchase_at : null,
            'avg_per_month' => $this->averagePerMonth($purchaseCount, $firstPurchaseAt),
        ];
    }

    private function averagePerMonth(int $purchaseCount, ?string $firstPurchaseAt): ?float
    {
        if ($purchaseCount < 1 || $firstPurchaseAt === null) {
            return null;
        }

        $days = Carbon::parse($firstPurchaseAt)->diffInDays(now());
        $months = $days / 30;

        return $months >= 1
            ? round($purchaseCount / $months, 1)
            : null;
    }
}
