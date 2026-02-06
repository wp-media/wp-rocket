<?php

// Use current time for all calculations to ensure consistency.
$now = time();

return [
	'test_data' => [
		'shouldReturnTrueWhenExpiringWithin30Days' => [
			'config'   => [
				'license_expiration' => $now + ( 25 * DAY_IN_SECONDS ), // Expires in 25 days.
				'duration_in_days'   => 30,
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenExpiringAfter30Days' => [
			'config'   => [
				'license_expiration' => $now + ( 35 * DAY_IN_SECONDS ), // Expires in 35 days.
				'duration_in_days'   => 30,
			],
			'expected' => false,
		],
		'shouldReturnTrueWhenExpiringWithin1Day'   => [
			'config'   => [
				'license_expiration' => $now + ( 12 * HOUR_IN_SECONDS ), // Expires in 12 hours.
				'duration_in_days'   => 1,
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenExpiringAfter1Day'   => [
			'config'   => [
				'license_expiration' => $now + ( 2 * DAY_IN_SECONDS ), // Expires in 2 days.
				'duration_in_days'   => 1,
			],
			'expected' => false,
		],
		'shouldReturnTrueWhenAlreadyExpired'       => [
			'config'   => [
				'license_expiration' => $now - ( 5 * DAY_IN_SECONDS ), // Expired 5 days ago.
				'duration_in_days'   => 30,
			],
			'expected' => false,
		],
		'shouldReturnTrueWhenExpiringExactlyAt30Days' => [
			'config'   => [
				'license_expiration' => $now + ( 30 * DAY_IN_SECONDS ), // Expires in exactly 30 days.
				'duration_in_days'   => 30,
			],
			'expected' => true, // 30 days IS within 30 days (boundary included with >=).
		],
		'shouldReturnTrueWhenExpiringWithin7Days' => [
			'config'   => [
				'license_expiration' => $now + ( 5 * DAY_IN_SECONDS ), // Expires in 5 days.
				'duration_in_days'   => 7,
			],
			'expected' => true,
		],
		'shouldReturnFalseWhenAutoRenewEnabled' => [
			'config'   => [
				'license_expiration' => $now + ( 1 * DAY_IN_SECONDS ), // Expires in 1 day.
				'duration_in_days'   => 7,
				'is_auto_renew'      => true,
			],
			'expected' => false, // Auto-renew enabled always returns false.
		],
	],
];

