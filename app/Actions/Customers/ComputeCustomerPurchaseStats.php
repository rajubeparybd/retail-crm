<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Compiles a customer's purchase history aggregates in a single query.
 *
 * Walk-in sales (those with a null customer_id) are excluded automatically
 * because the aggregate is scoped to this customer's sales. The "last
 * purchase date" is the exact maximum created_at among the customer's sales.
 */
class ComputeCustomerPurchaseStats
{
    /**
     * @return array{
     *     purchase_count: int,
     *     total_spent: string,
     *     first_purchase_at: string|null,
     *     last_purchase_at: string|null,
     *     avg_per_month: float|null,
     * }
     */
    public function execute(Customer $customer): array
    {
        /** @var \stdClass{purchase_count: int|string, total_spent: int|string|null, first_purchase_at: string|null, last_purchase_at: string|null} $agg */
        $agg = DB::table('sales')
            ->where('customer_id', $customer->id)
            ->selectRaw(
                'COUNT(*) AS purchase_count, '
                . 'COALESCE(SUM(total), 0) AS total_spent, '
                . 'MIN(created_at) AS first_purchase_at, '
                . 'MAX(created_at) AS last_purchase_at',
            )
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

    /**
     * Purchases per month over the customer's tenure (first purchase to now).
     * Returns null when there are no purchases, or when the tenure is shorter
     * than one month (a rate there would mislead rather than inform).
     */
    private function averagePerMonth(int $purchaseCount, ?string $firstPurchaseAt): ?float
    {
        if ($purchaseCount < 1 || $firstPurchaseAt === null) {
            return null;
        }

        $months = Carbon::parse($firstPurchaseAt)->floatDiffInMonths(now());

        return $months >= 1
            ? round($purchaseCount / $months, 1)
            : null;
    }
}
