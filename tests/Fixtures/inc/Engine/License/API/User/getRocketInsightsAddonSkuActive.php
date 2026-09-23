<?php

return [
	'testShouldReturnFreeSkuWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'expected' => 'perf-monitor-free',
	],
	'testShouldReturnFreeSkuWhenActiveSkuNotSet' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [],
		],
		'expected' => 'perf-monitor-free',
	],
	'testShouldReturnActiveSkuWhenSet' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [
				'active_sku' => 'perf-monitor-starter',
			],
		],
		'expected' => 'perf-monitor-starter',
	],
	'testShouldReturnEmptyStringWhenActiveSkuIsEmptyString' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [
				'active_sku' => '',
			],
		],
		'expected' => '',
	],
];
