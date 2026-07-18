<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LostCustomerController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::middleware(['role:admin'])->group(function (): void {
        Route::get('customers/lost-customers', [LostCustomerController::class, 'index'])->name('customers.lost-customers.index');
        Route::put('customers/lost-customers/{customer}', [LostCustomerController::class, 'update'])->name('customers.lost-customers.update');
    });

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
});
