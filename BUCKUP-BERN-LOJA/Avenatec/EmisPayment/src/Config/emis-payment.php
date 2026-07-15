<?php

return [
    'active'           => env('EMIS_PAYMENT_ACTIVE', false),
    'reference_prefix' => env('EMIS_PAYMENT_REFERENCE_PREFIX', 'BERNO'),
    'frame_token'      => env('EMIS_PAYMENT_FRAME_TOKEN'),
    'terminal_id'      => env('EMIS_PAYMENT_TERMINAL_ID'),
    'mobile_mode'      => env('EMIS_PAYMENT_MOBILE_MODE', 'PAYMENT'),
    'qrcode_mode'      => env('EMIS_PAYMENT_QRCODE_MODE', 'PAYMENT'),
    'card_mode'        => env('EMIS_PAYMENT_CARD_MODE', 'DISABLED'),
    'endpoint'         => env('EMIS_PAYMENT_ENDPOINT', 'https://pagamentonline.emis.co.ao/online-payment-gateway/portal/frameToken'),
    'frame_host'       => env('EMIS_PAYMENT_FRAME_HOST', 'https://pagamentonline.emis.co.ao/online-payment-gateway/webframe/frame?token='),
];
