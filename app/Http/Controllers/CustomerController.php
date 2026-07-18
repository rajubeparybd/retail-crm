<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Customers\ComputeCustomerPurchaseStats;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('sales as purchase_count')
            ->withSum('sales as total_spent', 'total')
            ->withMax('sales as last_purchase_at', 'created_at')
            ->orderByDesc('last_purchase_at')
            ->orderByDesc('customers.created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'purchase_count' => $customer->purchase_count,
                'total_spent' => $customer->total_spent !== null
                    ? number_format((float) $customer->total_spent, 2, '.', '')
                    : null,
                'last_purchase_at' => $customer->last_purchase_at,
            ]);

        return Inertia::render('customers/index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    public function show(Customer $customer): Response
    {
        $stats = app(ComputeCustomerPurchaseStats::class)->execute($customer);

        $sales = $customer->sales()
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('customers/show', [
            'customer' => $customer,
            'stats' => $stats,
            'sales' => $sales,
        ]);
    }
}
