<?php

return [
	'shouldReturnOriginalValueWhenNoTransient' => [
		'config'   => [],
		'expected' => [
			'cdn_zone' => null,
		],
	],

	'shouldReturnOriginalValueWhenSubscriptionCancelled' => [
		'config'   => [
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => false,
				'cdn_url'                       => '',
				'subscription_next_date_update' => 0,
				'subscription_status'           => 'cancelled',
			],
		],
		'expected' => [
			'cdn_zone' => null,
		],
	],

	'shouldReturnOriginalValueWhenSubscriptionRunningButNoCdnUrl' => [
		'config'   => [
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => '',
				'subscription_next_date_update' => '+30 days',
				'subscription_status'           => 'running',
			],
		],
		'expected' => [
			'cdn_zone' => null,
		],
	],

	'shouldReturnAllZoneWhenSubscriptionRunning' => [
		'config'   => [
			'subscription_data' => [
				'id'                            => 12345,
				'is_active'                     => true,
				'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
				'subscription_next_date_update' => '+30 days',
				'subscription_status'           => 'running',
			],
		],
		'expected' => [
			'cdn_zone' => [ 'all' ],
		],
	],
];
