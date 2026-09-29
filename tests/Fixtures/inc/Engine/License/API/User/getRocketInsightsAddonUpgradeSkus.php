<?php

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'      => 'perf-monitor-free',
				'upgrades' => [ 'perf-monitor-starter', 'perf-monitor-pro' ],
			],
			(object) [
				'sku' => 'perf-monitor-starter',
			],
		],
	],
];

return [
	'testShouldReturnEmptyArrayWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'sku'      => 'perf-monitor-free',
		'expected' => [],
	],
	'testShouldReturnEmptyArrayWhenSkuNotFound' => [
		'data'     => $user_with_plans,
		'sku'      => 'nonexistent-sku',
		'expected' => [],
	],
	'testShouldReturnEmptyArrayWhenUpgradesPropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => [],
	],
	'testShouldReturnUpgradeSkusWhenSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-free',
		'expected' => [ 'perf-monitor-starter', 'perf-monitor-pro' ],
	],
];
