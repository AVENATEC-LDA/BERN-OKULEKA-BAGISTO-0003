<?php

use Avenatec\EmisPayment\Payment\EmisPayment;

return [
    'emis_payment' => [
        'code'        => 'emis_payment',
        'title'       => 'EMIS - Multicaixa Express',
        'description' => 'Pague com MULTICAIXA Express, QR Code ou Pagar Online.',
        'class'       => EmisPayment::class,
        'active'      => false,
        'sort'        => 2,
    ],
];
