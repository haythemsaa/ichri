<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp Business API integration
    |
    */

    'enabled' => env('WHATSAPP_ENABLED', false),

    'api' => [
        'url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    ],

    'webhook' => [
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'ichri_whatsapp_verify_token'),
    ],

    'features' => [
        'auto_reply' => env('WHATSAPP_AUTO_REPLY_ENABLED', true),
        'order_placement' => env('WHATSAPP_ORDER_PLACEMENT_ENABLED', false), // Phase 3.0
        'catalog_sharing' => env('WHATSAPP_CATALOG_SHARING_ENABLED', false), // Phase 3.0
        'delivery_updates' => env('WHATSAPP_DELIVERY_UPDATES_ENABLED', false), // Phase 3.0
    ],

    'timeouts' => [
        'api_request' => env('WHATSAPP_API_TIMEOUT', 30),
        'webhook_processing' => env('WHATSAPP_WEBHOOK_TIMEOUT', 60),
    ],

];
