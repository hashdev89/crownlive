<?php

return [
    [
        'key' => 'sales.payment_methods.koko',
        'name' => 'KOKO Payments',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'title',
                'title' => 'Title',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => true,
            ],
            [
                'name' => 'description',
                'title' => 'Description',
                'type' => 'textarea',
                'channel_based' => true,
                'locale_based' => true,
            ],
            [
                'name' => 'image',
                'title' => 'Logo',
                'type' => 'image',
                'channel_based' => false,
                'locale_based' => false,
                'validation' => 'mimes:bmp,jpeg,jpg,png,webp',
            ],
            [
                'name' => 'merchant_id',
                'title' => 'Merchant ID',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'api_key',
                'title' => 'API Key',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'private_key',
                'title' => 'Private Key',
                'type' => 'textarea',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'public_key',
                'title' => 'Public Key',
                'type' => 'textarea',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'mobile',
                'title' => 'Gateway Mobile',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'password',
                'title' => 'Gateway Password',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'callback_base_url',
                'title' => 'Callback Base URL (Optional)',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'url',
                'info' => 'Override base URL for callbacks. Leave empty to use current domain. Required for local testing with ngrok/tunneling.',
                'example' => 'https://crowngallery.lk or https://leguminous-unhumbly-clare.ngrok-free.dev',
            ],
            [
                'name' => 'active',
                'title' => 'Enable',
                'type' => 'boolean',
                'channel_based' => false,
                'locale_based' => false,
            ],
            [
                'name'    => 'sort',
                'title'   => 'Sort Order',
                'type'    => 'select',
                'options' => [
                    [
                        'title' => '1',
                        'value' => 1,
                    ], [
                        'title' => '2',
                        'value' => 2,
                    ], [
                        'title' => '3',
                        'value' => 3,
                    ], [
                        'title' => '4',
                        'value' => 4,
                    ],
                ],
                'channel_based' => false,
                'locale_based' => false,
            ]
        ]
    ]
];

