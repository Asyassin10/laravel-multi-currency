<?php

return [
    'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
    
    'supported_currencies' => [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
        'SAR' => ['symbol' => 'ر.س', 'name' => 'Saudi Riyal'],
        'AED' => ['symbol' => 'د.إ', 'name' => 'UAE Dirham'],
        'EGP' => ['symbol' => 'ج.م', 'name' => 'Egyptian Pound'],
    ],

    'exchange_rate_provider' => env('EXCHANGE_RATE_PROVIDER', 'exchangerate-api'),
    
    'providers' => [
        'exchangerate-api' => [
            'url' => 'https://api.exchangerate-api.com/v4/latest/{base}',
            'api_key' => env('EXCHANGE_RATE_API_KEY'),
        ],
        'currencyapi' => [
            'url' => 'https://api.currencyapi.com/v3/latest',
            'api_key' => env('CURRENCY_API_KEY'),
        ],
    ],

    'cache_duration' => env('CURRENCY_CACHE_DURATION', 3600), // 1 hour
    
    'auto_detect_currency' => env('AUTO_DETECT_CURRENCY', true),
    
    'session_key' => 'selected_currency',
    'cookie_name' => 'selected_currency',
    'cookie_duration' => 60 * 24 * 30, // 30 days
];
