<?php

return [
	'shouldBailWhenUserLacksCapability' => [
		'config'   => [
			'user_role' => 'subscriber',
			'token'     => '1234567890123456789012345678901234567890',
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldBailWhenNoToken' => [
		'config'   => [
			'user_role' => 'administrator',
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldBailWhenSubscriptionIsActive' => [
		'config'   => [
			'user_role'         => 'administrator',
			'token'             => '1234567890123456789012345678901234567890',
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => 'https://example.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldBailWhenInactiveButCdnUrlEmpty' => [
		'config'   => [
			'user_role'         => 'administrator',
			'token'             => '1234567890123456789012345678901234567890',
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => '',
				'subscription_status'           => 'cancelled',
				'subscription_next_date_update' => '+30 days',
			],
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldBailWhenSubscriptionIdMissing' => [
		'config'   => [
			'user_role'         => 'administrator',
			'token'             => '1234567890123456789012345678901234567890',
			'subscription_data' => [
				'is_active'                     => false,
				'cdn_url'                       => 'https://example.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldBailWhenActivationApiFails' => [
		'config'   => [
			'user_role'          => 'administrator',
			'token'              => '1234567890123456789012345678901234567890',
			'subscription_data'  => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => 'https://example.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
			'activation_success' => false,
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldEnableCdnWhenRetryActivationSucceeds' => [
		'config'   => [
			'user_role'                         => 'administrator',
			'token'                             => '1234567890123456789012345678901234567890',
			'subscription_data'                 => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
			'activation_success'                => true,
			'subscription_data_after_activation' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
		],
		'expected' => [
			'cdn_enabled' => true,
			'cdn_url'     => 'https://abcd1234.delivery.rocketcdn.me',
		],
	],

	'shouldBailWhenNoTokenAndUserEndpointDown' => [
		'config'   => [
			'user_role'         => 'administrator',
			// No token saved.
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
			// User endpoint returns empty (simulates endpoint down).
		],
		'expected' => [
			'cdn_enabled' => false,
		],
	],

	'shouldRetryActivationWithTokenFromUserEndpoint' => [
		'config'   => [
			'user_role'                         => 'administrator',
			// No token saved locally.
			'user_data'                         => (object) [
				'rocketcdn' => (object) [
					'cdn_token' => '1234567890123456789012345678901234567890',
				],
			],
			'subscription_data'                 => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
			'activation_success'                => true,
			'subscription_data_after_activation' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '+30 days',
			],
		],
		'expected' => [
			'cdn_enabled' => true,
			'cdn_url'     => 'https://abcd1234.delivery.rocketcdn.me',
		],
	],
];
