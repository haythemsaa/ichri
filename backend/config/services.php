<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'enabled' => env('TWILIO_ENABLED', false),
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'cartunisie' => [
        'merchant_id' => env('CARTUNISIE_MERCHANT_ID'),
        'api_key' => env('CARTUNISIE_API_KEY'),
        'secret_key' => env('CARTUNISIE_SECRET_KEY'),
    ],

    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'api_key' => env('PAYME_API_KEY'),
    ],

    'ddinar' => [
        'merchant_id' => env('DDINAR_MERCHANT_ID'),
        'api_key' => env('DDINAR_API_KEY'),
    ],

    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST', 'localhost'),
        'port' => env('ELASTICSEARCH_PORT', 9200),
        'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
    ],

];
