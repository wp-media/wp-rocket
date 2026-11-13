<?php

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'    => 'perf-monitor-free',
				'button' => (object) [ 'label' => 'Your plan' ],
			],
			(object) [
				'sku'    => 'perf-monitor-starter',
				'button' => (object) [ 'label' => 'Get Advanced' ],
			],
			(object) [
				'sku'    => 'perf-monitor-pro',
				'button' => (object) [ 'label' => 'Custom Label' ],
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
	'testShouldReturnTranslatedYourPlanLabel' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-free',
		'expected' => 'Your plan',
	],
	'testShouldReturnTranslatedGetRocketInsightsLabel' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => 'Get Rocket Insights',
	],
	'testShouldReturnRawLabelForUnknownLabel' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-pro',
		'expected' => 'Custom Label',
	],
];
