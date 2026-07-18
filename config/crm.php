<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Lost Customer Detection
    |--------------------------------------------------------------------------
    |
    | Number of days without a purchase before a customer is considered "lost"
    | and becomes eligible for a re-engagement campaign. This value is used by
    | the crm:detect-lost-customers command and can be overridden per-run via
    | the --days option.
    |
    */

    'lost_customer_days' => (int) env('CRM_LOST_CUSTOMER_DAYS', 90),

];
