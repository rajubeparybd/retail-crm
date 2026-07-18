<?php

declare(strict_types=1);

namespace App\Actions\Crm;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class IncrementEmployeeKpi
{
    /**
     * Atomically increment the employee's KPI score.
     */
    public function execute(User $employee, int $amount = 1): void
    {
        DB::table('users')
            ->where('id', $employee->id)
            ->increment('kpi_score', $amount);
    }
}
