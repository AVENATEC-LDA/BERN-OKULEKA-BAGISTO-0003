<?php

return [
    [
        'key'  => 'sales.carriers.fleet',
        'name' => 'Fleet Delivery',
        'info' => 'Configurações do método de entrega via Fleet.ao',
        'sort' => 10,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'Título exibido no checkout',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'       => 'active',
                'title'      => 'Ativo',
                'type'       => 'boolean',
                'validation' => 'required',
            ],
            [
                'name'       => 'environment',
                'title'      => 'Ambiente',
                'type'       => 'select',
                'options'    => [
                    ['title' => 'Sandbox', 'value' => 'sandbox'],
                    ['title' => 'Produção', 'value' => 'production'],
                ],
                'validation' => 'required',
            ],
            [
                'name'       => 'api_key',
                'title'      => 'Fleet API Key (Bearer)',
                'type'       => 'password',
                'validation' => 'required',
            ],
            [
                'name'       => 'webhook_secret',
                'title'      => 'Fleet Webhook Secret (HMAC)',
                'type'       => 'password',
                'validation' => 'required',
            ],
            [
                'name'       => 'origin_address_line',
                'title'      => 'Endereço de origem (armazém/loja)',
                'type'       => 'text',
                'validation' => 'required',
            ],
            [
                'name'       => 'origin_city',
                'title'      => 'Cidade de origem',
                'type'       => 'text',
                'validation' => 'required',
            ],
            [
                'name'       => 'origin_lat',
                'title'      => 'Latitude de origem',
                'type'       => 'text',
                'validation' => 'required|numeric',
            ],
            [
                'name'       => 'origin_lng',
                'title'      => 'Longitude de origem',
                'type'       => 'text',
                'validation' => 'required|numeric',
            ],
            [
                'name'       => 'origin_contact_name',
                'title'      => 'Nome do contacto (loja)',
                'type'       => 'text',
                'validation' => 'required',
            ],
            [
                'name'       => 'origin_contact_phone',
                'title'      => 'Telefone do contacto (loja)',
                'type'       => 'text',
                'validation' => 'required',
            ],
            [
                'name'       => 'default_parcel_type',
                'title'      => 'Tipo de encomenda padrão',
                'type'       => 'select',
                'options'    => [
                    ['title' => 'Pequena embalagem', 'value' => 'small_package'],
                    ['title' => 'Média embalagem', 'value' => 'medium_package'],
                    ['title' => 'Grande embalagem', 'value' => 'large_package'],
                    ['title' => 'Frágil', 'value' => 'fragile'],
                ],
                'validation' => 'required',
            ],
        ],
    ],
];
