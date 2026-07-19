<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PurchaseSuccessful;
use App\Mail\CustomerInvoice;
use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

#[Tries(3)]
#[Backoff(60)]
class SendInvoiceToCustomerListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PurchaseSuccessful $event): void
    {
        $sale = $event->sale;
        $customer = $sale->customer;

        if ($customer instanceof Customer && filled($customer->email)) {
            Mail::to($customer->email)->send(new CustomerInvoice($sale));
        }
    }
}
