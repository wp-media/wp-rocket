<?php

return [
	'inProgressUrlShowsAnalyzing' => [
		'config' => [
			'row_data' => [
				'url' => 'http://example.com/in-progress',
				'title' => 'In Progress Test',
				'status' => 'in-progress',
				'is_mobile' => 0,
				'job_id' => 'test_in_progress_' . uniqid(),
				'queue_name' => 'rocket-performance-monitoring',
				'retries' => 1,
				'data' => '{}',
				'score' => 0,
				'report_url' => '',
				'error_message' => '',
				'submitted_at' => gmdate( 'Y-m-d H:i:s' ),
				'last_accessed' => gmdate( 'Y-m-d H:i:s' ),
				'modified' => gmdate( 'Y-m-d H:i:s', time() - 300 ), // 5 minutes ago
				'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
			],
		],
		'expected' => [
			'should_show_analyzing' => true,
			'contains' => [
				'wpr-loading-container',
				'orange-loading.svg',
			],
			'not_contains' => [
				'ago',
			],
		],
	],
	'pendingUrlShowsAnalyzing' => [
		'config' => [
			'row_data' => [
				'url' => 'http://example.com/pending',
				'title' => 'Pending Test',
				'status' => 'pending',
				'is_mobile' => 0,
				'job_id' => 'test_pending_' . uniqid(),
				'queue_name' => 'rocket-performance-monitoring',
				'retries' => 1,
				'data' => '{}',
				'score' => 0,
				'report_url' => '',
				'error_message' => '',
				'submitted_at' => gmdate( 'Y-m-d H:i:s' ),
				'last_accessed' => gmdate( 'Y-m-d H:i:s' ),
				'modified' => gmdate( 'Y-m-d H:i:s', time() - 120 ), // 2 minutes ago
				'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
			],
		],
		'expected' => [
			'should_show_analyzing' => true,
			'contains' => [
				'wpr-loading-container',
				'orange-loading.svg',
			],
			'not_contains' => [
				'ago',
			],
		],
	],
	'toSubmitUrlShowsAnalyzing' => [
		'config' => [
			'row_data' => [
				'url' => 'http://example.com/to-submit',
				'title' => 'To Submit Test',
				'status' => 'to-submit',
				'is_mobile' => 0,
				'job_id' => 'test_to_submit_' . uniqid(),
				'queue_name' => 'rocket-performance-monitoring',
				'retries' => 1,
				'data' => '{}',
				'score' => 0,
				'report_url' => '',
				'error_message' => '',
				'submitted_at' => gmdate( 'Y-m-d H:i:s' ),
				'last_accessed' => gmdate( 'Y-m-d H:i:s' ),
				'modified' => gmdate( 'Y-m-d H:i:s', time() - 60 ), // 1 minute ago
				'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
			],
		],
		'expected' => [
			'should_show_analyzing' => true,
			'contains' => [
				'wpr-loading-container',
				'orange-loading.svg',
			],
			'not_contains' => [
				'ago',
			],
		],
	],
	'completedUrlShowsTimeAgo' => [
		'config' => [
			'row_data' => [
				'url' => 'http://example.com/completed',
				'title' => 'Completed Test',
				'status' => 'completed',
				'is_mobile' => 0,
				'job_id' => 'test_completed_' . uniqid(),
				'queue_name' => 'rocket-performance-monitoring',
				'retries' => 1,
				'data' => json_encode( [ 'score' => 85 ] ),
				'score' => 85,
				'report_url' => 'http://gtmetrix.com/report/123',
				'error_message' => '',
				'submitted_at' => gmdate( 'Y-m-d H:i:s', time() - 3600 ),
				'last_accessed' => gmdate( 'Y-m-d H:i:s', time() - 3600 ),
				'modified' => gmdate( 'Y-m-d H:i:s', time() - 3600 ), // 1 hour ago
				'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
			],
		],
		'expected' => [
			'should_show_analyzing' => false,
			'contains' => [
				'ago',
				'85',
			],
			'not_contains' => [
				'Analyzing your page (~1 min)',
				'wpr-loading-container',
			],
		],
	],
	'failedUrlShowsTimeAgo' => [
		'config' => [
			'row_data' => [
				'url' => 'http://example.com/failed',
				'title' => 'Failed Test',
				'status' => 'failed',
				'is_mobile' => 0,
				'job_id' => 'test_failed_' . uniqid(),
				'queue_name' => 'rocket-performance-monitoring',
				'retries' => 1,
				'data' => '{}',
				'score' => 0,
				'report_url' => '',
				'error_message' => 'Test error',
				'submitted_at' => gmdate( 'Y-m-d H:i:s', time() - 7200 ),
				'last_accessed' => gmdate( 'Y-m-d H:i:s', time() - 7200 ),
				'modified' => gmdate( 'Y-m-d H:i:s', time() - 7200 ), // 2 hours ago
				'next_retry_time' => gmdate( 'Y-m-d H:i:s' ),
			],
		],
		'expected' => [
			'should_show_analyzing' => false,
			'contains' => [
				'ago',
				'wpr-failed-score',
			],
			'not_contains' => [
				'Analyzing your page (~1 min)',
				'wpr-loading-container',
			],
		],
	],
];