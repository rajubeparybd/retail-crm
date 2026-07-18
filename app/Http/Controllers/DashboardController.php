<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const int LOW_STOCK_THRESHOLD = 5;

    private const int RECENT_SALES_LIMIT = 5;

    public function index(): Response
    {
        $recentSales = Sale::query()
            ->with(['items.product', 'user', 'customer'])
            ->latest()
            ->limit(self::RECENT_SALES_LIMIT)
            ->get();

        return Inertia::render('dashboard', [
            'stats' => [
                'today_revenue' => (string) Sale::query()
                    ->whereDate('created_at', now()->toDateString())
                    ->sum('total'),
                'total_sales' => Sale::query()->count(),
                'low_stock_count' => Product::query()
                    ->where('stock_quantity', '<', self::LOW_STOCK_THRESHOLD)
                    ->count(),
            ],
            'recentSales' => $recentSales,
        ]);
    }
}
