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
					'price'      => '$4.99',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-active-promo',
				'promo' => (object) [
					'expires_at' => $future_timestamp,
					'price'      => '$4.99',
				],
			],
			(object) [
				'sku'   => 'perf-monitor-promo-no-price',
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
		'sku'      => 'perf-monitor-active-promo',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenSkuNotFound' => [
		'data'     => $user_with_plans,
		'sku'      => 'nonexistent-sku',
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
	'testShouldReturnEmptyStringWhenPricePropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-promo-no-price',
		'expected' => '',
	],
	'testShouldReturnPromoPriceWhenActivePromo' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-active-promo',
		'expected' => '$4.99',
	],
];
