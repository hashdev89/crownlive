<?php

return [
    [
        'key' => 'sales.payment_methods.onepay',
        'name' => 'Onepay Payments',
        'sort' => 2,
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
                'name' => 'app_id',
                'title' => 'App ID',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'hash_salt',
                'title' => 'Hash Salt',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
            ],
            [
                'name' => 'app_token',
                'title' => 'App Token',
                'type' => 'text',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1',
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

