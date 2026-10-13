<?php

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'   => 'perf-monitor-free',
				'price' => '$0',
			],
			(object) [
				'sku'   => 'perf-monitor-starter',
				'price' => '$9.99',
			],
			(object) [
				'sku' => 'perf-monitor-no-price',
			],
		],
	],
];

return [
	'testShouldReturnEmptyStringWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'sku'      => 'perf-monitor-free',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenSkuNotFound' => [
		'data'     => $user_with_plans,
		'sku'      => 'nonexistent-sku',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenPricePropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-price',
		'expected' => '',
	],
	'testShouldReturnFreePlanPrice' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-free',
		'expected' => '$0',
	],
	'testShouldReturnPaidPlanPrice' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => '$9.99',
	],
];
