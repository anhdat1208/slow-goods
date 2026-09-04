<?php

return [
    'webhook_token' => env('SEPAY_WEBHOOK_TOKEN'),
    // Must match order_number prefix (CheckoutService uses SG-…)
    'pattern' => env('SEPAY_MATCH_PATTERN', 'SG-'),
];
