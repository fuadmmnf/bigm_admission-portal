<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Store Credentials
    |--------------------------------------------------------------------------
    */
    'store_id' => env('SSLCOMMERZ_STORE_ID', ''),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    */
    'sandbox' => filter_var(env('SSLCOMMERZ_SANDBOX', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    'api' => [
        'sandbox' => [
            'initiate' => 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php',
            'validate' => 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php',
        ],
        'production' => [
            'initiate' => 'https://securepay.sslcommerz.com/gwprocess/v4/api.php',
            'validate' => 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Amount
    |--------------------------------------------------------------------------
    */
    'default_amount' => (float) env('PAYMENT_DEFAULT_AMOUNT', 0),

    /*
    |--------------------------------------------------------------------------
    | Payment Callback Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'success' => env('SSLCOMMERZ_SUCCESS_URL', '/payment/callback/success'),
        'failed' => env('SSLCOMMERZ_FAIL_URL', '/payment/callback/failed'),
        'cancel' => env('SSLCOMMERZ_CANCEL_URL', '/payment/callback/cancel'),
        'ipn' => env('SSLCOMMERZ_IPN_URL', '/payment/ipn'),
    ],

    // Sandbox can use legacy/simple callback paths for local development convenience.
    'sandbox_routes' => [
        'success' => env('SSLCOMMERZ_SANDBOX_SUCCESS_URL', '/payment/success'),
        'failed' => env('SSLCOMMERZ_SANDBOX_FAIL_URL', '/payment/failed'),
        'cancel' => env('SSLCOMMERZ_SANDBOX_CANCEL_URL', '/payment/cancel'),
        'ipn' => env('SSLCOMMERZ_SANDBOX_IPN_URL', '/payment/ipn'),
    ],

];

