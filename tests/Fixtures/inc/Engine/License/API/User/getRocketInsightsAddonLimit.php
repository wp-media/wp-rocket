<?php

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'   => 'perf-monitor-free',
				'limit' => 10,
			],
			(object) [
				'sku'   => 'perf-monitor-starter',
				'limit' => 50,
			],
			(object) [
				'sku' => 'perf-monitor-no-limit',
			],
		],
	],
];

return [
	'testShouldReturnDefaultLimitWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'sku'      => 'perf-monitor-free',
		'expected' => 10,
	],
	'testShouldReturnDefaultLimitWhenSkuNotFound' => [
		'data'     => $user_with_plans,
		'sku'      => 'nonexistent-sku',
		'expected' => 10,
	],
	'testShouldReturnDefaultLimitWhenLimitPropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-limit',
		'expected' => 10,
	],
	'testShouldReturnLimitFromPlan' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-free',
		'expected' => 10,
	],
	'testShouldReturnHigherLimitForPaidPlan' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => 50,
	],
];
