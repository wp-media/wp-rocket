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
					'name'       => 'Old Offer',
					'price'      => '$4.99',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-active-promo',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
					'name'       => 'Launch Offer',
					'price'      => '$4.99',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-no-expires',
				'promo' => (object) [
					'name'  => 'Launch Offer',
					'price' => '$4.99',
				],
			],
		],
	],
];

return [
	'testShouldReturnFalseWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'sku'      => 'perf-monitor-active-promo',
		'expected' => false,
	],
	'testShouldReturnFalseWhenSkuNotFound' => [
		'data'     => $user_with_plans,
		'sku'      => 'nonexistent-sku',
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoPropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-promo',
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoHasNoExpiresAt' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-expires',
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoIsExpired' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-expired-promo',
		'expected' => false,
	],
	'testShouldReturnTrueWhenPromoIsActive' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-active-promo',
		'expected' => true,
	],
];
