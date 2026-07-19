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

    /*
   |--------------------------------------------------------------------------
   | Lost Stock Threshold
   |--------------------------------------------------------------------------
   |
   | Number of units of stock below which a product is considered "low stock"
   | and will be flagged in the dashboard. This value is used by the dashboard
   | and can be overridden via the CRM_LOST_STOCK_THRESHOLD environment variable.
   |
   */

    'lost_stock_threshold' => (int) env('CRM_LOST_STOCK_THRESHOLD', 5),

    /*
  |--------------------------------------------------------------------------
  | Recent Sales Limit
  |--------------------------------------------------------------------------
  |
  | Number of recent sales to display on the dashboard. This value is used by the dashboard
  |
  */
    'recent_sales_limit' => (int) env('CRM_RECENT_SALES_LIMIT', 5),

];
