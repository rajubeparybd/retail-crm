<?php

declare(strict_types=1);

use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('pos', [SaleController::class, 'create'])->name('sales.create');
    Route::get('pos/customer', [SaleController::class, 'findCustomer'])->name('sales.customer');
    Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
});
