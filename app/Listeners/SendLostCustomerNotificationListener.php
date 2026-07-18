<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CustomerBecameLost;
use App\Mail\LostCustomerPromotionalMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

#[Tries(3)]
#[Backoff(60)]
class SendLostCustomerNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CustomerBecameLost $event): void
    {
        Mail::to($event->customer->email)
            ->queue(new LostCustomerPromotionalMail($event->customer));
    }
}
