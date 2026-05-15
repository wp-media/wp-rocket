<?php

return [
	'shouldReturnOriginalValueWhenCdnTypeIsNotRocketcdn' => [
		'config'   => [
			'cdn_type'          => 'other',
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_next_date_update' => '+30 days',
				'subscription_status'           => 'running',
			],
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnOriginalValueWhenNoSubscriptionData' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'subscription_data' => [
				'id'                            => 0,
				'is_active'                     => false,
				'cdn_url'                       => '',
				'subscription_next_date_update' => 0,
				'subscription_status'           => '',
			],
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnOriginalValueWhenSubscriptionCancelled' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => '',
				'subscription_next_date_update' => 0,
				'subscription_status'           => 'cancelled',
			],
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnOriginalValueWhenSubscriptionRunningButNoCdnUrl' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => '',
				'subscription_next_date_update' => '+30 days',
				'subscription_status'           => 'running',
			],
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnCdnUrlWhenSubscriptionRunning' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_next_date_update' => '+30 days',
				'subscription_status'           => 'running',
			],
		],
		'expected' => [
			'cdn_cnames' => [ 'https://abcd1234.delivery.rocketcdn.me' ],
		],
	],
];
