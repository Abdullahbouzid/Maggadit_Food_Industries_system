<?php

declare(strict_types=1);

return [
    /*
    |----------------------------------------------------------------------
    | Inventory Reservation TTL
    |----------------------------------------------------------------------
    | Number of hours before an uncommitted stock reservation expires.
    | Default: 24 hours. Can be overridden per-order.
    |
    */
    'reservation_ttl_hours' => env('INVENTORY_RESERVATION_TTL_HOURS', 24),

    /*
    |----------------------------------------------------------------------
    | Low Stock Notification Threshold (hours)
    |----------------------------------------------------------------------
    | Runs daily to check for items below reorder level.
    |
    */
    'low_stock_check_enabled' => env('INVENTORY_LOW_STOCK_CHECK_ENABLED', true),

    /*
    |----------------------------------------------------------------------
    | Near Expiry Notification Days
    |----------------------------------------------------------------------
    | Number of days before expiry to trigger notifications.
    |
    */
    'near_expiry_days' => env('INVENTORY_NEAR_EXPIRY_DAYS', 30),
];
