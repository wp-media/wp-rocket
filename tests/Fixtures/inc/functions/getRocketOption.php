<?php

return [
	'settings' => [
		'automatic_cleanup_frequency' => 'weekly',
		'cdn'                         => 1,
		'cdn_cnames'                  => [ 'https://rocketcdn.me' ],
		'cdn_zone'                    => [ 'all' ],
		'do_cloudflare'               => 1,
		'purge_cron_interval'         => 10,
		'purge_cron_unit'             => 'HOUR_IN_SECONDS',
	],

	'test_data' => [
		[
			'option'   => 'automatic_cleanup_frequency',
			'default'  => 'daily',
			'expected' => 'weekly',
		],
		[
			'option'   => 'cdn',
			'default'  => 0,
			'expected' => 1,
		],
		[
			'option'   => 'cdn_cnames',
			'default'  => [],
			'expected' => [ 'https://rocketcdn.me' ],
		],
		[
			'option'   => 'cdn_zone',
			'default'  => [],
			'expected' => [ 'all' ],
		],
		[
			'option'   => 'do_cloudflare',
			'default'  => 0,
			'expected' => 1,
		],

		// These don't exist.
		[
			'option'   => 'doesnotexist',
			'default'  => false,
			'expected' => false,
		],
		[
			'option'   => 'control_heartbeat',
			'default'  => 0,
			'expected' => 0,
		],
		[
			'option'   => 'heartbeat_site_behavior',
			'default'  => 'reduce_periodicity',
			'expected' => 'reduce_periodicity',
		],
	],
];
