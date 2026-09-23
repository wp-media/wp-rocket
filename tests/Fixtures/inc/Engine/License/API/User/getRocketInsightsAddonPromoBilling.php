<?php

$future_timestamp = time() + 86400;
$past_timestamp   = time() - 86400;
$known_billing    = 'Launch price valid for the first 12 months, after which standard pricing applies.';
$custom_billing   = 'Valid for 6 months.';

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku' => 'perf-monitor-no-promo',
			],
			(object) [
				'sku'   => 'perf-monitor-expired-promo',
				'promo' => (object) [
					'expires_at' => $past_timestamp,
					'billing'    => $known_billing,
				],
			],
			(object) [
				'sku'   => 'perf-monitor-known-billing',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
					'billing'    => $known_billing,
				],
			],
			(object) [
				'sku'   => 'perf-monitor-custom-billing',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
					'billing'    => $custom_billing,
				],
			],
			(object) [
				'sku'   => 'perf-monitor-promo-no-billing',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
				],
			],
		],
	],
];

return [
	'testShouldReturnEmptyStringWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'sku'      => 'perf-monitor-known-billing',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenPromoNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-promo',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenPromoExpired' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-expired-promo',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenBillingPropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-promo-no-billing',
		'expected' => '',
	],
	'testShouldReturnTranslatedKnownPromoBilling' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-known-billing',
		'expected' => $known_billing,
	],
	'testShouldReturnRawBillingForUnknownPromoBilling' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-custom-billing',
		'expected' => $custom_billing,
	],
];
