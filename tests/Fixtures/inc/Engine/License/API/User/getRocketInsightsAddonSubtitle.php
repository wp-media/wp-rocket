<?php

$known_subtitle   = 'See how your top pages perform and quickly spot and optimize what slows your site down.';
$custom_subtitle  = 'Track your most important pages performance.';

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'      => 'perf-monitor-free',
				'subtitle' => $known_subtitle,
			],
			(object) [
				'sku'      => 'perf-monitor-starter',
				'subtitle' => $custom_subtitle,
			],
			(object) [
				'sku' => 'perf-monitor-no-subtitle',
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
	'testShouldReturnEmptyStringWhenSubtitlePropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-subtitle',
		'expected' => '',
	],
	'testShouldReturnTranslatedKnownSubtitle' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-free',
		'expected' => $known_subtitle,
	],
	'testShouldReturnRawSubtitleForUnknownSubtitle' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => $custom_subtitle,
	],
];
