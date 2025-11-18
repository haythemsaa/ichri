<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Twilio SMS Service
    'twilio' => [
        'enabled' => env('TWILIO_ENABLED', false),
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_PHONE_NUMBER'),
        'verify_sid' => env('TWILIO_VERIFY_SID'),
    ],

    // Firebase Push Notifications
    'firebase' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'database_url' => env('FIREBASE_DATABASE_URL'),
    ],

    // OneSignal Push Notifications
    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
    ],

    // Google Maps
    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
        // Social Auth
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    // Facebook Social Auth
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],

    // Payment Gateways - Flouci (Tunisia)
    'flouci' => [
        'app_token' => env('FLOUCI_APP_TOKEN'),
        'app_secret' => env('FLOUCI_APP_SECRET'),
        'sandbox' => env('FLOUCI_SANDBOX', true),
        'url' => env('FLOUCI_SANDBOX', true) 
            ? 'https://developers.flouci.com/api/'
            : 'https://api.flouci.com/',
    ],

    // D17 Payment (Tunisia)
    'd17' => [
        'api_key' => env('D17_API_KEY'),
        'api_secret' => env('D17_API_SECRET'),
        'sandbox' => env('D17_SANDBOX', true),
    ],

    // Stripe (International)
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // Digital Services Providers - Ooredoo
    'ooredoo' => [
        'api_key' => env('OOREDOO_API_KEY'),
        'api_secret' => env('OOREDOO_API_SECRET'),
        'sandbox' => env('OOREDOO_SANDBOX', true),
        'url' => env('OOREDOO_SANDBOX', true)
            ? 'https://sandbox-api.ooredoo.tn/'
            : 'https://api.ooredoo.tn/',
    ],

    // Orange Tunisia
    'orange' => [
        'api_key' => env('ORANGE_API_KEY'),
        'api_secret' => env('ORANGE_API_SECRET'),
        'sandbox' => env('ORANGE_SANDBOX', true),
        'url' => env('ORANGE_SANDBOX', true)
            ? 'https://sandbox.orange.tn/api/'
            : 'https://api.orange.tn/',
    ],

    // Tunisie Telecom
    'tunisie_telecom' => [
        'api_key' => env('TT_API_KEY'),
        'api_secret' => env('TT_API_SECRET'),
        'sandbox' => env('TT_SANDBOX', true),
        'url' => env('TT_SANDBOX', true)
            ? 'https://sandbox-api.tunisietelecom.tn/'
            : 'https://api.tunisietelecom.tn/',
    ],

    // STEG (Electricity)
    'steg' => [
        'api_key' => env('STEG_API_KEY'),
        'api_secret' => env('STEG_API_SECRET'),
        'sandbox' => env('STEG_SANDBOX', true),
        'url' => env('STEG_SANDBOX', true)
            ? 'https://sandbox-api.steg.com.tn/'
            : 'https://api.steg.com.tn/',
    ],

    // SONEDE (Water)
    'sonede' => [
        'api_key' => env('SONEDE_API_KEY'),
        'api_secret' => env('SONEDE_API_SECRET'),
        'sandbox' => env('SONEDE_SANDBOX', true),
        'url' => env('SONEDE_SANDBOX', true)
            ? 'https://sandbox-api.sonede.com.tn/'
            : 'https://api.sonede.com.tn/',
    ],

    // ElasticSearch
    'elasticsearch' => [
        'hosts' => [env('ELASTICSEARCH_HOST', 'http://localhost:9200')],
        'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'ichri'),
    ],

    // Sentry Error Tracking
    'sentry' => [
        'dsn' => env('SENTRY_LARAVEL_DSN'),
        'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.2),
    ],

];
