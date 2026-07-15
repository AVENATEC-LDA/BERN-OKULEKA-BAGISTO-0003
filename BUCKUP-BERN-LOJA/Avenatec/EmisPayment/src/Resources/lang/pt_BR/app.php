<?php

return [
    'admin' => [
        'system' => [
            'emis-payment'          => 'EMIS - Multicaixa Express',
            'emis-payment-info'     => 'Gateway de pagamento EMIS GPO para Angola.',
            'title'                 => 'Titulo',
            'description'           => 'Descricao',
            'status'                => 'Activo',
            'reference-prefix'      => 'Prefixo da Referencia',
            'reference-prefix-info' => 'Ate 6 caracteres alfanumericos. Exemplo: BERNO + 1234 = BERNO1234.',
            'frame-token'           => 'Frame Token',
            'frame-token-info'      => 'Token do comerciante obtido no portal EMIS.',
            'terminal-id'           => 'ID do Terminal',
            'terminal-id-info'      => 'ID do terminal fornecido pela EMIS.',
            'mobile-mode'           => 'Multicaixa Express',
            'qrcode-mode'           => 'QR Code',
            'card-mode'             => 'Cartao Multicaixa',
            'card-mode-info'        => 'Requer certificacao EGR. Nao activar sem aprovacao EMIS.',
            'endpoint'              => 'Endpoint EMIS',
            'frame-host'            => 'Host do iframe EMIS',
            'sort-order'            => 'Ordem de exibicao',
        ],
    ],

    'shop' => [
        'invalid-session'   => 'Sessao de pagamento invalida. Por favor tente novamente.',
        'initiation-failed' => 'Nao foi possivel iniciar o pagamento EMIS. Por favor tente novamente.',
        'expired-session'   => 'Sessao de pagamento expirada. Por favor tente novamente.',
        'order-not-found'   => 'Pedido nao encontrado.',
    ],
];
