<?php

return [

	'testShouldScheduleNewCheckEventWhenSubscriptionRunning' => [
		'config' => [
			'status'   => 'running',
			'wp_rocket_settings' => [
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://rocketcdn.me' ],
				'cdn_zone'   => [ 'all' ],
			],
		],
		'expected' => [
			'cron_is_scheduled' => true,
			'settings' => [
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://rocketcdn.me' ],
				'cdn_zone'   => [ 'all' ],
			]
		],
	],

	'testShouldDisableCDNWhenSubscriptionCancelled' => [
		'config' => [
			'status'   => 'cancelled',
			'wp_rocket_settings' => [
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://rocketcdn.me' ],
				'cdn_zone'   => [ 'all' ],
			],
		],
		'expected' => [
			'cron_is_scheduled' => false,
			'settings' => [
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			]
		],
	]
];
