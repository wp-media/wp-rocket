<?php

return [
	'shouldBailWhenUserLacksCapability' => [
		'config'   => [
			'user_role' => 'subscriber',
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldBailWhenNoTokenExists' => [
		'config'   => [
			'user_role'         => 'administrator',
			'subscription_data' => [
				'is_active' => false,
				'cdn_url'   => '',
			],
			// No token set - user never checked out
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldBailWhenNotOnSettingsPage' => [
		'config'   => [
			'user_role'      => 'administrator',
			'current_screen' => 'dashboard',
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldBailWhenWhiteLabelAccount' => [
		'config'   => [
			'user_role'   => 'administrator',
			'white_label' => true,
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldBailWhenSubscriptionIsActive' => [
		'config'   => [
			'user_role'         => 'administrator',
			'subscription_data' => [
				'is_active' => true,
				'cdn_url'   => 'https://example.rocketcdn.me',
			],
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldBailWhenInactiveButHasCdnUrl' => [
		'config'   => [
			'user_role'         => 'administrator',
			'subscription_data' => [
				'is_active' => false,
				'cdn_url'   => 'https://example.rocketcdn.me',
			],
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldBailWhenNoExpressCheckoutUrl' => [
		'config'   => [
			'user_role'         => 'administrator',
			'subscription_data' => [
				'is_active' => false,
				'cdn_url'   => '',
			],
			'user_data'         => [
				'rocketcdn' => [
					'button' => [
						'url' => '',
					],
				],
			],
		],
		'expected' => [
			'should_display' => false,
		],
	],

	'shouldDisplayNoticeWhenInactiveAndNoCdnUrlAndHasExpressCheckoutUrl' => [
		'config'   => [
			'user_role'         => 'administrator',
			'token'             => '1234567890123456789012345678901234567890',
			'subscription_data' => [
				'is_active' => false,
				'cdn_url'   => '',
			],
			'user_data'         => [
				'rocketcdn' => [
					'button' => [
						'url' => 'https://wp-rocket.me/express-checkout/?email=test@example.com&domain=example.com&product_sku=rocket-cdn&consumer_key=abc123',
					],
				],
			],
		],
		'expected' => [
			'should_display'               => true,
			'express_checkout_url_contains' => 'express-checkout',
		],
	],
];
