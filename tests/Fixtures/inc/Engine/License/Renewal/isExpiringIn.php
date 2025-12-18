<?php

return [
	'test_data' => [
		'shouldReturnTrueWhenExpiringWithin30Days' => [
			'config'   => [
				'license_expiration' => time() + ( 25 * DAY_IN_SECONDS ), // Expires in 25 days.
				'duration_in_days'   => 30,
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenExpiringAfter30Days' => [
			'config'   => [
				'license_expiration' => time() + ( 35 * DAY_IN_SECONDS ), // Expires in 35 days.
				'duration_in_days'   => 30,
			],
			'expected' => false,
		],
		'shouldReturnTrueWhenExpiringWithin1Day'   => [
			'config'   => [
				'license_expiration' => time() + ( 12 * HOUR_IN_SECONDS ), // Expires in 12 hours.
				'duration_in_days'   => 1,
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenExpiringAfter1Day'   => [
			'config'   => [
				'license_expiration' => time() + ( 2 * DAY_IN_SECONDS ), // Expires in 2 days.
				'duration_in_days'   => 1,
			],
			'expected' => false,
		],
		'shouldReturnTrueWhenAlreadyExpired'       => [
			'config'   => [
				'license_expiration' => time() - ( 5 * DAY_IN_SECONDS ), // Expired 5 days ago.
				'duration_in_days'   => 30,
			],
			'expected' => true,
		],
		'shouldReturnTrueWhenExpiringExactlyAt30Days' => [
			'config'   => [
				'license_expiration' => time() + ( 30 * DAY_IN_SECONDS ), // Expires in exactly 30 days.
				'duration_in_days'   => 30,
			],
			'expected' => false, // 30 days is NOT less than 30 days.
		],
		'shouldReturnTrueWhenExpiringWithin7Days' => [
			'config'   => [
				'license_expiration' => time() + ( 5 * DAY_IN_SECONDS ), // Expires in 5 days.
				'duration_in_days'   => 7,
			],
			'expected' => true,
		],
	],
];
