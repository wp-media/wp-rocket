<?php

return [
	'shouldBailOutWhenParameterNotSet' => [
		'config'   => [
			'parameter_set' => false,
			'user_role'     => 'administrator',
		],
		'expected' => [
			'token_stored' => false,
		],
	],

    'shouldBailOutWhenUserLacksPermission' => [
        'config'   => [
            'parameter_set' => true,
            'user_role'     => 'subscriber',
        ],
        'expected' => [
            'token_stored' => false,
        ],
    ],

    'shouldBailOutWhenUserDataMissing' => [
        'config'   => [
            'parameter_set' => true,
            'user_role'     => 'administrator',
            'user_data'     => [
                'rocketcdn' => [
                    'cdn_url' => 'https://example.rocketcdn.me',
                ],
            ],
        ],
        'expected' => [
            'token_stored'     => false,
            'expects_redirect' => true,
        ],
    ],

    'shouldBailOutWhenTokenLengthInvalid' => [
        'config'   => [
            'parameter_set' => true,
            'user_role'     => 'administrator',
            'user_data'     => [
                'rocketcdn' => [
                    'cdn_token'            => 'short_token',
                    'cdn_url'              => 'https://example.rocketcdn.me',
                    'rocketcdn_website_id' => 12345,
                ],
            ],
        ],
        'expected' => [
            'token_stored'     => false,
            'expects_redirect' => true,
        ],
    ],

    'shouldActivateSubscriptionAndEnableCDN' => [
        'config'   => [
            'parameter_set' => true,
            'user_role'     => 'administrator',
            'user_data'     => [
                'rocketcdn' => [
                    'cdn_token'            => '1234567890123456789012345678901234567890',
                    'cdn_url'              => 'https://example.rocketcdn.me',
                    'rocketcdn_website_id' => 12345,
                ],
            ],
            'api_activation_success' => true,
            'api_subscription_data'  => [
                'subscription_status'           => 'running',
                'subscription_next_date_update' => '+30 days',
                'is_active'                     => true,
                'cdn_url'                       => 'https://example.rocketcdn.me',
            ],
        ],
        'expected' => [
            'token_value'      => '1234567890123456789012345678901234567890',
            'cdn_enabled'      => true,
            'cdn_url'          => 'https://example.rocketcdn.me',
            'cdn_state'        => 'rocketcdn_paid',
            'expects_redirect' => true,
        ],
    ],
];
