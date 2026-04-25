<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Store Credentials
    |--------------------------------------------------------------------------
    | Credentials from your SSLCommerz merchant dashboard.
    | Use sandbox credentials for local/staging and live for production.
    */
    'store_id' => env('SSLCOMMERZ_STORE_ID', ''),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    | When true, all requests go to sandbox.sslcommerz.com.
    | Set to false in production.
    */
    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),

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
    | Payment Callback Routes
    |--------------------------------------------------------------------------
    | Override these if your routes use different names.
    */
    'routes' => [
        'success' => env('SSLCOMMERZ_SUCCESS_URL', '/payment/success'),
        'failed' => env('SSLCOMMERZ_FAIL_URL', '/payment/failed'),
        'cancel' => env('SSLCOMMERZ_CANCEL_URL', '/payment/cancel'),
        'ipn' => env('SSLCOMMERZ_IPN_URL', '/payment/ipn'),
    ],

];

