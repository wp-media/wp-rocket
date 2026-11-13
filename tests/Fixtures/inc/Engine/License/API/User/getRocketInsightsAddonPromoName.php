<?php

$future_timestamp = time() + 86400;
$past_timestamp   = time() - 86400;

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
					'name'       => 'Launch Offer',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-known-name',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
					'name'       => 'Launch Offer',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-custom-name',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
					'name'       => 'Black Friday',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-promo-no-name',
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
		'sku'      => 'perf-monitor-known-name',
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
	'testShouldReturnEmptyStringWhenNamePropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-promo-no-name',
		'expected' => '',
	],
	'testShouldReturnTranslatedLaunchOfferName' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-known-name',
		'expected' => 'Launch Offer',
	],
	'testShouldReturnRawNameForUnknownPromoName' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-custom-name',
		'expected' => 'Black Friday',
	],
];
