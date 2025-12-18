<?php

return [
	'test_data' => [
		'shouldAddHomepageWhenNoUrlsAndExpiringWithin1Day' => [
			'config'   => [
				'existing_urls'      => 0,
				'license_expiration' => time() + ( 12 * HOUR_IN_SECONDS ), // Expires in 12 hours.
				'interval'           => 1, // 1 day.
			],
			'expected' => [
				'database_entries' => 2, // Desktop + Mobile.
				'homepage_added'   => true,
			],
		],
		'shouldNotAddHomepageWhenUrlsAlreadyExist'         => [
			'config'   => [
				'existing_urls'      => 2,
				'license_expiration' => time() + ( 12 * HOUR_IN_SECONDS ), // Expires in 12 hours.
				'interval'           => 1, // 1 day.
			],
			'expected' => [
				'database_entries' => 2, // Only existing URLs.
				'homepage_added'   => false,
			],
		],
		'shouldNotAddHomepageWhenNotExpiringYet'           => [
			'config'   => [
				'existing_urls'      => 0,
				'license_expiration' => time() + ( 5 * DAY_IN_SECONDS ), // Expires in 5 days.
				'interval'           => 1, // 1 day threshold.
			],
			'expected' => [
				'database_entries' => 0, // No URLs added.
				'homepage_added'   => false,
			],
		],
		'shouldNotAddHomepageWhenIntervalIsZero'           => [
			'config'   => [
				'existing_urls'      => 0,
				'license_expiration' => time() + ( 12 * HOUR_IN_SECONDS ), // Expires in 12 hours.
				'interval'           => 0, // Feature disabled.
			],
			'expected' => [
				'database_entries' => 0, // No URLs added.
				'homepage_added'   => false,
			],
		],
		'shouldAddHomepageWhenExpiringWithin7Days'         => [
			'config'   => [
				'existing_urls'      => 0,
				'license_expiration' => time() + ( 5 * DAY_IN_SECONDS ), // Expires in 5 days.
				'interval'           => 7, // 7 day threshold.
			],
			'expected' => [
				'database_entries' => 2, // Desktop + Mobile.
				'homepage_added'   => true,
			],
		],
		'shouldAddHomepageWhenAlreadyExpired'              => [
			'config'   => [
				'existing_urls'      => 0,
				'license_expiration' => time() - ( 2 * DAY_IN_SECONDS ), // Expired 2 days ago.
				'interval'           => 1, // 1 day.
			],
			'expected' => [
				'database_entries' => 2, // Desktop + Mobile.
				'homepage_added'   => true,
			],
		],
	],
];
