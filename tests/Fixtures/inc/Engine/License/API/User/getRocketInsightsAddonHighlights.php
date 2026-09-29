<?php

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'        => 'perf-monitor-free',
				'highlights' => [
					'Up to 10 pages tracked',
					'Automatic performance monitoring',
				],
			],
			(object) [
				'sku'        => 'perf-monitor-starter',
				'highlights' => [
					'Unlimited on-demand tests',
					'Full GTmetrix performance reports',
					'Custom highlight text',
				],
			],
			(object) [
				'sku' => 'perf-monitor-no-highlights',
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
	'testShouldReturnEmptyArrayWhenHighlightsPropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-highlights',
		'expected' => [],
	],
	'testShouldTranslateKnownHighlights' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-free',
		'expected' => [
			sprintf( 'Up to %1$s%2$s pages%3$s tracked', '<strong>', '10', '</strong>' ),
			sprintf( 'Automatic %1$sperformance monitoring%2$s', '<strong>', '</strong>' ),
		],
	],
	'testShouldTranslateAllKnownHighlightsAndPassThroughCustomOnes' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => [
			sprintf( 'Unlimited %1$son-demand tests%2$s', '<strong>', '</strong>' ),
			sprintf( 'Full %1$sGTmetrix performance reports%2$s', '<strong>', '</strong>' ),
			'Custom highlight text',
		],
	],
];
