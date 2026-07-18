<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Sales\ProcessCheckout;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\Sales\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function index(): Response
    {
        $sales = Sale::query()
            ->with(['items.product', 'user', 'customer'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('sales/index', [
            'sales' => $sales,
        ]);
    }

    public function create(): Response
    {
        $products = Product::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('sales/create', [
            'products' => $products,
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($request, $data): void {
                $customer = Customer::firstOrCreate(
                    ['email' => $data['customer_email']],
                    [
                        'name' => $data['customer_name'],
                        'phone' => $data['customer_phone'] ?? null,
                    ],
                );

                app(ProcessCheckout::class)->execute(
                    $request->user(),
                    $data['items'],
                    $customer,
                );
            });
        } catch (InsufficientStockException $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return back()->withInput();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Sale completed.'),
        ]);

        return to_route('sales.index');
    }

    /**
     * Look up a customer by email for the POS autofill.
     */
    public function findCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $customer = Customer::query()
            ->where('email', $request->query('email'))
            ->first();

        return response()->json(['customer' => $customer]);
    }
}
