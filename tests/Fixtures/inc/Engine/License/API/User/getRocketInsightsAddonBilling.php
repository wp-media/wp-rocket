<?php

$known_billing   = '* Billed monthly. You can cancel at any time, each month started is due.';
$custom_billing  = '* Billed annually.';

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'     => 'perf-monitor-starter',
				'billing' => $known_billing,
			],
			(object) [
				'sku'     => 'perf-monitor-pro',
				'billing' => $custom_billing,
			],
			(object) [
				'sku' => 'perf-monitor-no-billing',
			],
		],
	],
];

return [
	'testShouldReturnEmptyStringWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'sku'      => 'perf-monitor-starter',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenSkuNotFound' => [
		'data'     => $user_with_plans,
		'sku'      => 'nonexistent-sku',
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenBillingPropertyNotSet' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-no-billing',
		'expected' => '',
	],
	'testShouldReturnTranslatedKnownBilling' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-starter',
		'expected' => $known_billing,
	],
	'testShouldReturnRawBillingForUnknownBilling' => [
		'data'     => $user_with_plans,
		'sku'      => 'perf-monitor-pro',
		'expected' => $custom_billing,
	],
];
