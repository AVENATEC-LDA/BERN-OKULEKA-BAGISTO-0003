<?php

return [
    [
        'key'    => 'sales.payment_methods.paypal_smart_button',
        'name'   => 'PayPal Smart Button',
        'info'   => 'PayPal Smart Button configuration',
        'sort'   => 5,
        'fields' => [
            [
                'name'          => 'active',
                'title'         => 'Status',
                'type'          => 'boolean',
                'default_value' => true,
                'channel_based' => true,
            ],

            [
                'name'          => 'title',
                'title'         => 'Title',
                'type'          => 'text',
                'default_value' => 'PayPal Smart Button',
                'channel_based' => true,
                'locale_based'  => true,
            ],

            [
                'name'          => 'description',
                'title'         => 'Description',
                'type'          => 'textarea',
                'default_value' => 'Secure payments via PayPal Smart Button',
                'channel_based' => true,
                'locale_based'  => true,
            ],

            [
                'name'          => 'client_id',
                'title'         => 'Client ID',
                'type'          => 'password',
                'channel_based' => true,
            ],

            [
                'name'          => 'client_secret',
                'title'         => 'Client Secret',
                'type'          => 'password',
                'channel_based' => true,
            ],

            [
                'name'          => 'sandbox',
                'title'         => 'Sandbox Mode',
                'type'          => 'boolean',
                'default_value' => true,
                'channel_based' => true,
            ],

            [
                'name'          => 'kwanza_aoa_to_usd_rate',
                'title'         => 'Kwanza AOA → USD Conversion Rate',
                'type'          => 'text',
                'validation'    => 'numeric',
                'default_value' => '1000',
                'channel_based' => true,
                'info'          => 'Rate for converting AOA to USD (e.g., 1000 means 1 AOA = 0.001 USD)',
            ],

            [
                'name'          => 'accepted_currencies',
                'title'         => 'Accepted Currencies',
                'type'          => 'text',
                'default_value' => 'USD',
                'channel_based' => true,
                'info'          => 'Comma-separated list of accepted currencies',
            ],

            [
                'name'          => 'sort',
                'title'         => 'Sort Order',
                'type'          => 'text',
                'default_value' => '5',
            ],
        ],
    ],

    [
        'key'    => 'sales.payment_methods.paypal_standard',
        'name'   => 'PayPal Standard',
        'info'   => 'PayPal Standard configuration',
        'sort'   => 6,
        'fields' => [
            [
                'name'          => 'active',
                'title'         => 'Status',
                'type'          => 'boolean',
                'default_value' => true,
                'channel_based' => true,
            ],

            [
                'name'          => 'title',
                'title'         => 'Title',
                'type'          => 'text',
                'default_value' => 'PayPal Standard',
                'channel_based' => true,
                'locale_based'  => true,
            ],

            [
                'name'          => 'description',
                'title'         => 'Description',
                'type'          => 'textarea',
                'default_value' => 'Secure payments via PayPal Standard',
                'channel_based' => true,
                'locale_based'  => true,
            ],

            [
                'name'          => 'business_account',
                'title'         => 'Business Account Email',
                'type'          => 'text',
                'validation'    => 'email',
                'channel_based' => true,
                'info'          => 'Your PayPal Business Account email (required for IPN)',
            ],

            [
                'name'          => 'sandbox',
                'title'         => 'Sandbox Mode',
                'type'          => 'boolean',
                'default_value' => true,
                'channel_based' => true,
            ],

            [
                'name'          => 'kwanza_aoa_to_usd_rate',
                'title'         => 'Kwanza AOA → USD Conversion Rate',
                'type'          => 'text',
                'validation'    => 'numeric',
                'default_value' => '1000',
                'channel_based' => true,
                'info'          => 'Rate for converting AOA to USD (e.g., 1000 means 1 AOA = 0.001 USD)',
            ],

            [
                'name'          => 'sort',
                'title'         => 'Sort Order',
                'type'          => 'text',
                'default_value' => '6',
            ],
        ],
    ],
];

