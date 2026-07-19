<?php

declare(strict_types=1);

namespace App\Actions\Crm;

use App\Models\User;

class IncrementEmployeeKpi
{
    public function execute(User $employee, int $amount = 1): void
    {
        $employee->increment('kpi_score', $amount);
        $employee->refresh();
    }
}
