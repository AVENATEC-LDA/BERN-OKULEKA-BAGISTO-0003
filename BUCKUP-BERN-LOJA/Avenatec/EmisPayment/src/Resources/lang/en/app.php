<?php

return [
    'admin' => [
        'system' => [
            'emis-payment'          => 'EMIS - Multicaixa Express',
            'emis-payment-info'     => 'EMIS GPO payment gateway for Angola.',
            'title'                 => 'Title',
            'description'           => 'Description',
            'status'                => 'Active',
            'reference-prefix'      => 'Reference Prefix',
            'reference-prefix-info' => 'Up to 6 alphanumeric characters. Example: BERNO + 1234 = BERNO1234.',
            'frame-token'           => 'Frame Token',
            'frame-token-info'      => 'Merchant token from the EMIS portal kiosk area.',
            'terminal-id'           => 'Terminal ID',
            'terminal-id-info'      => 'Point-of-sale terminal ID provided by EMIS.',
            'mobile-mode'           => 'Multicaixa Express',
            'qrcode-mode'           => 'QR Code',
            'card-mode'             => 'Multicaixa Card',
            'card-mode-info'        => 'Requires EGR certification. Do not activate without EMIS approval.',
            'endpoint'              => 'EMIS Endpoint',
            'frame-host'            => 'EMIS Frame Host',
            'sort-order'            => 'Sort Order',
        ],
    ],

    'shop' => [
        'invalid-session'   => 'Invalid payment session. Please try again.',
        'initiation-failed' => 'Unable to start EMIS payment. Please try again.',
        'expired-session'   => 'Payment session expired. Please try again.',
        'order-not-found'   => 'Order not found.',
    ],
];
