<?php

return [
	'testShouldReturnWPErrorWhenNoPermissions' => [
		'config'   => [
			'has_permission' => false,
			'input' => null,
			'items' => [],
		],
		'expected' => [
			'is_error'        => true,
			'summary'         => [
				'global_score'    => 0,
				'pages_monitored' => 0,
				'status'          => 'no-url',
				'is_running'      => false,
			],
			'results_count'   => 0,
			'has_metric_data' => false,
		],
	],
	'testShouldReturnEmptyResultsWhenNoData' => [
		'config'   => [
			'has_permission' => true,
			'input' => null,
			'items' => [],
		],
		'expected' => [
			'is_error'        => false,
			'summary'         => [
				'global_score'    => 0,
				'pages_monitored' => 0,
				'status'          => 'no-url',
				'is_running'      => false,
			],
			'results_count'   => 0,
			'has_metric_data' => false,
		],
	],
	'testShouldReturnResultsWithoutMetricData' => [
		'config'   => [
			'has_permission' => true,
			'input' => null,
			'items' => [
				[
					'url'       => 'https://example.com/page1',
					'title'     => 'Page 1',
					'is_mobile' => false,
					'job_id'    => 'test_123',
					'status'    => 'completed',
					'score'     => 85,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":85}}}',
				],
				[
					'url'       => 'https://example.com/page2',
					'title'     => 'Page 2',
					'is_mobile' => true,
					'job_id'    => 'test_456',
					'status'    => 'completed',
					'score'     => 92,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":92}}}',
				],
			],
		],
		'expected' => [
			'is_error'        => false,
			'summary'         => [
				'global_score'    => 89,
				'pages_monitored' => 2,
				'status'          => 'complete',
				'is_running'      => false,
			],
			'results_count'   => 2,
			'has_metric_data' => false,
		],
	],
	'testShouldReturnResultsWithMetricData' => [
		'config'   => [
			'has_permission' => true,
			'input' => [
				'include_metrics' => true,
			],
			'items' => [
				[
					'url'         => 'https://example.com/',
					'title'       => 'Home',
					'is_mobile'   => false,
					'job_id'      => 'test_home',
					'status'      => 'completed',
					'score'       => 90,
					'data'        => '{"status":"complete","data":{"data":{"performance_score":90}}}',
					'metric_data' => '{"lcp":1200,"fcp":800,"cls":0.05}',
				],
			],
		],
		'expected' => [
			'is_error'        => false,
			'summary'         => [
				'global_score'    => 90,
				'pages_monitored' => 1,
				'status'          => 'complete',
				'is_running'      => false,
			],
			'results_count'   => 1,
			'has_metric_data' => true,
		],
	],
	'testShouldReturnInProgressWhenJobsRunning' => [
		'config'   => [
			'has_permission' => true,
			'input' => null,
			'items' => [
				[
					'url'       => 'https://example.com/page1',
					'title'     => 'Page 1',
					'is_mobile' => false,
					'job_id'    => 'test_123',
					'status'    => 'completed',
					'score'     => 90,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":90}}}',
				],
				[
					'url'       => 'https://example.com/page2',
					'title'     => 'Page 2',
					'is_mobile' => true,
					'job_id'    => 'test_456',
					'status'    => 'in-progress',
					'score'     => 0,
					'data'      => '{"status":"in-progress"}',
				],
			],
		],
		'expected' => [
			'is_error'        => false,
			'summary'         => [
				'global_score'    => 90,
				'pages_monitored' => 2,
				'status'          => 'in-progress',
				'is_running'      => true,
			],
			'results_count'   => 2,
			'has_metric_data' => false,
		],
	],
];
