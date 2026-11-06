<?php

return [
	'testShouldReturnEmptyResultsWhenStatusIsNoUrl' => [
		'config'   => [
			'input'             => null,
			'global_score_data' => [
				'score'      => 0,
				'pages_num'  => 0,
				'status'     => 'no-url',
				'is_running' => false,
			],
			'query_results'     => [],
		],
		'expected' => [
			'query_called' => false,
			'query_args'   => [],
			'result'       => [
				'summary' => [
					'global_score'    => 0,
					'pages_monitored' => 0,
					'status'          => 'no-url',
					'is_running'      => false,
				],
				'results' => [],
			],
		],
	],
	'testShouldQueryWithoutMetricDataWhenInputIsNull' => [
		'config'   => [
			'input'             => null,
			'global_score_data' => [
				'score'      => 85,
				'pages_num'  => 5,
				'status'     => 'completed',
				'is_running' => false,
			],
			'query_results'     => [
				[
					'url'        => 'https://example.com/',
					'title'      => 'Home',
					'is_mobile'  => false,
					'status'     => 'completed',
					'data'       => '{}',
					'modified'   => '2026-03-20 10:00:00',
					'score'      => 90,
					'report_url' => 'https://gtmetrix.com/reports/example',
				],
			],
		],
		'expected' => [
			'query_called' => true,
			'query_args'   => [
				'fields' => [
					'url',
					'title',
					'is_mobile',
					'status',
					'data',
					'modified',
					'score',
					'report_url',
				],
			],
			'result'       => [
				'summary' => [
					'global_score'    => 85,
					'pages_monitored' => 5,
					'status'          => 'completed',
					'is_running'      => false,
				],
				'results' => [
					[
						'url'        => 'https://example.com/',
						'title'      => 'Home',
						'is_mobile'  => false,
						'status'     => 'completed',
						'data'       => '{}',
						'modified'   => '2026-03-20 10:00:00',
						'score'      => 90,
						'report_url' => 'https://gtmetrix.com/reports/example',
					],
				],
			],
		],
	],
	'testShouldQueryWithoutMetricDataWhenIncludeMetricsIsFalse' => [
		'config'   => [
			'input'             => [
				'include_metrics' => false,
			],
			'global_score_data' => [
				'score'      => 75,
				'pages_num'  => 3,
				'status'     => 'completed',
				'is_running' => false,
			],
			'query_results'     => [
				[
					'url'        => 'https://example.com/about',
					'title'      => 'About',
					'is_mobile'  => true,
					'status'     => 'completed',
					'data'       => '{}',
					'modified'   => '2026-03-21 12:00:00',
					'score'      => 75,
					'report_url' => 'https://gtmetrix.com/reports/about',
				],
			],
		],
		'expected' => [
			'query_called' => true,
			'query_args'   => [
				'fields' => [
					'url',
					'title',
					'is_mobile',
					'status',
					'data',
					'modified',
					'score',
					'report_url',
				],
			],
			'result'       => [
				'summary' => [
					'global_score'    => 75,
					'pages_monitored' => 3,
					'status'          => 'completed',
					'is_running'      => false,
				],
				'results' => [
					[
						'url'        => 'https://example.com/about',
						'title'      => 'About',
						'is_mobile'  => true,
						'status'     => 'completed',
						'data'       => '{}',
						'modified'   => '2026-03-21 12:00:00',
						'score'      => 75,
						'report_url' => 'https://gtmetrix.com/reports/about',
					],
				],
			],
		],
	],
	'testShouldQueryWithMetricDataWhenIncludeMetricsIsTrue' => [
		'config'   => [
			'input'             => [
				'include_metrics' => true,
			],
			'global_score_data' => [
				'score'      => 92,
				'pages_num'  => 2,
				'status'     => 'completed',
				'is_running' => false,
			],
			'query_results'     => [
				[
					'url'         => 'https://example.com/contact',
					'title'       => 'Contact',
					'is_mobile'   => false,
					'status'      => 'completed',
					'data'        => '{}',
					'modified'    => '2026-03-22 14:00:00',
					'score'       => 92,
					'report_url'  => 'https://gtmetrix.com/reports/contact',
					'metric_data' => [
						'lcp' => 1.2,
						'fcp' => 0.8,
					],
				],
			],
		],
		'expected' => [
			'query_called' => true,
			'query_args'   => [
				'fields' => [
					'url',
					'title',
					'is_mobile',
					'status',
					'data',
					'modified',
					'score',
					'report_url',
					'metric_data',
				],
			],
			'result'       => [
				'summary' => [
					'global_score'    => 92,
					'pages_monitored' => 2,
					'status'          => 'completed',
					'is_running'      => false,
				],
				'results' => [
					[
						'url'         => 'https://example.com/contact',
						'title'       => 'Contact',
						'is_mobile'   => false,
						'status'      => 'completed',
						'data'        => '{}',
						'modified'    => '2026-03-22 14:00:00',
						'score'       => 92,
						'report_url'  => 'https://gtmetrix.com/reports/contact',
						'metric_data' => [
							'lcp' => 1.2,
							'fcp' => 0.8,
						],
					],
				],
			],
		],
	],
	'testShouldReturnCorrectSummaryWhenStatusIsInProgress' => [
		'config'   => [
			'input'             => null,
			'global_score_data' => [
				'score'      => 60,
				'pages_num'  => 10,
				'status'     => 'in-progress',
				'is_running' => true,
			],
			'query_results'     => [
				[
					'url'        => 'https://example.com/blog',
					'title'      => 'Blog',
					'is_mobile'  => false,
					'status'     => 'pending',
					'data'       => '{}',
					'modified'   => '2026-03-23 08:00:00',
					'score'      => null,
					'report_url' => null,
				],
			],
		],
		'expected' => [
			'query_called' => true,
			'query_args'   => [
				'fields' => [
					'url',
					'title',
					'is_mobile',
					'status',
					'data',
					'modified',
					'score',
					'report_url',
				],
			],
			'result'       => [
				'summary' => [
					'global_score'    => 60,
					'pages_monitored' => 10,
					'status'          => 'in-progress',
					'is_running'      => true,
				],
				'results' => [
					[
						'url'        => 'https://example.com/blog',
						'title'      => 'Blog',
						'is_mobile'  => false,
						'status'     => 'pending',
						'data'       => '{}',
						'modified'   => '2026-03-23 08:00:00',
						'score'      => null,
						'report_url' => null,
					],
				],
			],
		],
	],
];
