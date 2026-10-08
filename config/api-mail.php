<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Mail Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default API mail provider used when sending
    | notifications via ApiMailChannel or ApiMail facade. You can override
    | this dynamically on any message using ->provider('resend').
    |
    */

    'default' => env('API_MAIL_PROVIDER', 'hostinger'),

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | Default sender email address and name used by API mail providers if not
    | explicitly set on individual notification messages.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@avirataa.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Avirataa')),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Mail Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API credentials and settings for all supported email providers.
    |
    */

    'providers' => [

        'hostinger' => [
            'driver' => 'hostinger',
            'token' => env('HOSTINGER_MAIL_API_TOKEN', ''),
            'base_url' => env('HOSTINGER_MAIL_API_URL', 'https://api.mail.hostinger.com/api/v1'),
            'cache_ttl' => (int) env('HOSTINGER_MAIL_CACHE_TTL', 3600), // Cache mailbox resourceId lookup for 1 hour
        ],

        // Future providers can be configured here:
        // 'resend' => [
        //     'driver' => 'resend',
        //     'key' => env('RESEND_API_KEY'),
        // ],

    ],

];
