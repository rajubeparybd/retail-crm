<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Customers\AssignLostCustomer;
use App\Actions\Customers\FindLostCustomers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateLostCustomerAssignmentRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LostCustomerController extends Controller
{
    public function index(): Response
    {
        $days = (int) config('crm.lost_customer_days', 90);

        $lostCustomerIds = app(FindLostCustomers::class)->execute($days)->pluck('id');

        $customers = Customer::query()
            ->whereIn('id', $lostCustomerIds)
            ->with('assignedEmployee:id,name,kpi_score')
            ->withMax('sales as last_purchase_at', 'created_at')
            ->orderByDesc('last_purchase_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'last_purchase_at' => $customer->last_purchase_at,
                'assigned_employee' => $customer->assignedEmployee
                    ? ['id' => $customer->assignedEmployee->id, 'name' => $customer->assignedEmployee->name]
                    : null,
            ]);

        $employees = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'kpi_score']);

        return Inertia::render('customers/lost-customers/index', [
            'customers' => $customers,
            'employees' => $employees,
            'days' => $days,
        ]);
    }

    public function update(
        UpdateLostCustomerAssignmentRequest $request,
        Customer $customer,
    ): RedirectResponse {
        $employeeId = $request->validated('employee_id');

        $employee = $employeeId !== null
            ? User::findOrFail($employeeId)
            : null;

        app(AssignLostCustomer::class)->execute($customer, $employee);

        return back();
    }
}
