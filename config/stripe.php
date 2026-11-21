<?php

return [
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'currency' => env('STRIPE_CURRENCY', 'php'),
    'success_redirect_url' => env('STRIPE_SUCCESS_REDIRECT_URL'),
    'failed_redirect_url' => env('STRIPE_FAILED_REDIRECT_URL'),
];
