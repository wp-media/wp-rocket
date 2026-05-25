<?php

return [
	'shouldReturnOriginalValueWhenCdnTypeIsNotRocketcdn' => [
		'config'   => [
			'cdn_type'          => 'other',
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnOriginalValueWhenNoActiveSubscription' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'has_active_subscription' => false,
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnOriginalValueWhenSubscriptionRunningButNoCdnUrl' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'has_active_subscription' => true,
			'cdn_url' => '',
		],
		'expected' => [
			'cdn_cnames' => null,
		],
	],

	'shouldReturnAllZoneWhenSubscriptionRunning' => [
		'config'   => [
			'cdn_type'          => 'rocketcdn',
			'has_active_subscription' => true,
			'cdn_url' => 'https://abcd1234.delivery.rocketcdn.me',
		],
		'expected' => [
			'cdn_cnames' => [ 'https://abcd1234.delivery.rocketcdn.me' ],
		],
	],
];
