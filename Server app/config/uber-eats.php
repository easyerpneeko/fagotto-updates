<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Uber Eats API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Uber Eats API integration
    |
    */

    'client_id' => env('UBER_EATS_CLIENT_ID', ''),
    'client_secret' => env('UBER_EATS_CLIENT_SECRET', ''),
    'scope' => env('UBER_EATS_SCOPE', 'eats.order'),
    'store_id' => env('UBER_EATS_STORE_ID', ''),
    'webhook_url' => env('UBER_EATS_WEBHOOK_URL', ''),
    
    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    
    'base_url' => env('UBER_EATS_BASE_URL', 'https://api.uber.com'),
    'endpoints' => [
        'token' => '/v1/oauth/token',
        'orders' => '/v1/eats/orders',
        'store' => '/v1/eats/store',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */
    
    'default_prep_time' => env('UBER_EATS_DEFAULT_PREP_TIME', 15), // minutos
    'auto_accept' => env('UBER_EATS_AUTO_ACCEPT', false),
    'webhook_signature_key' => env('UBER_EATS_WEBHOOK_SIGNATURE_KEY', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    
    'cache_prefix' => 'uber_eats_',
    'token_cache_time' => 3600, // 1 hora en segundos
];
