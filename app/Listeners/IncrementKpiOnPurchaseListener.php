<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Crm\IncrementEmployeeKpi;
use App\Events\LostCustomerMadePurchase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;

#[Tries(3)]
#[Backoff(60)]
class IncrementKpiOnPurchaseListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LostCustomerMadePurchase $event): void
    {
        app(IncrementEmployeeKpi::class)->execute($event->employee);
    }
}
