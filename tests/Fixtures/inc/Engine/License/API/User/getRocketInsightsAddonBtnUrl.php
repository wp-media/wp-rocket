<?php

$base_url = 'https://wp-rocket.me/checkout/upgrade/';

$user_with_plans = (object) [
	'performance_monitoring' => (object) [
		'plans' => [
			(object) [
				'sku'    => 'perf-monitor-free',
				'button' => (object) [ 'label' => 'Your plan', 'url' => '' ],
			],
			(object) [
				'sku'    => 'perf-monitor-starter',
				'button' => (object) [
					'label' => 'Get Advanced',
					'url'   => $base_url,
				],
			],
		],
	],
];

$admin_url = 'http://example.com/wp-admin/options-general.php?page=wprocket&rocket_insights_upgrade=true#rocket_insights';

return [
	'testShouldReturnEmptyStringWhenPerformanceMonitoringNotSet' => [
		'config'   => [
			'data'      => (object) [],
			'sku'       => 'perf-monitor-free',
			'has_url'   => false,
			'admin_url' => '',
		],
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenSkuNotFound' => [
		'config'   => [
			'data'      => $user_with_plans,
			'sku'       => 'nonexistent-sku',
			'has_url'   => false,
			'admin_url' => '',
		],
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenButtonUrlIsEmpty' => [
		'config'   => [
			'data'      => $user_with_plans,
			'sku'       => 'perf-monitor-free',
			'has_url'   => false,
			'admin_url' => '',
		],
		'expected' => '',
	],
	'testShouldReturnUrlWithDashboardUrlQueryArg' => [
		'config'   => [
			'data'      => $user_with_plans,
			'sku'       => 'perf-monitor-starter',
			'has_url'   => true,
			'admin_url' => $admin_url,
		],
		'expected' => $base_url . '&dashboard_url=' . rawurlencode( $admin_url ),
	],
];
