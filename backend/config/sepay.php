<?php

return [
    'webhook_token' => env('SEPAY_WEBHOOK_TOKEN'),
    // HMAC secret from SePay dashboard (falls back to webhook_token if empty)
    'webhook_secret' => env('SEPAY_WEBHOOK_SECRET'),
    // Must match order_number prefix (CheckoutService uses SG-…)
    'pattern' => env('SEPAY_MATCH_PATTERN', 'SG-'),
];
