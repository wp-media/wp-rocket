<?php

return [
	'testShouldReturnWPErrorWhenNoPermissions' => [
		'config'   => [
			'has_permission' => false,
			'input'          => [
				'url' => 'https://example.com/test-page',
			],
			'existing_items' => [],
			'mock_http'      => false,
		],
		'expected' => [
			'is_error'   => true,
			'success'    => false,
			'status'     => 'failed',
			'hook_fired' => false,
		],
	],

	'testShouldReturnNotFoundWhenUrlNotTracked' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/not-tracked',
			],
			'existing_items' => [],
			'mock_http'      => false,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => false,
			'status'     => 'not_found',
			'error'      => 'URL is not tracked by Rocket Insights.',
			'hook_fired' => false,
		],
	],

	'testShouldReturnRunningWhenTestInProgress' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/in-progress-page',
			],
			'existing_items' => [
				[
					'url'        => 'https://example.com/in-progress-page',
					'title'      => 'In Progress Page',
					'is_mobile'  => true,
					'job_id'     => 'in-progress-job-id',
					'status'     => 'in-progress',
					'score'      => '',
					'report_url' => '',
					'is_blurred' => 0,
					'data'       => '{"status":"running"}',
				],
			],
			'mock_http'      => false,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => true,
			'status'     => 'running',
			'error'      => '',
			'hook_fired' => false,
		],
	],

	'testShouldRetestSuccessfully' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/completed-page',
			],
			'existing_items' => [
				[
					'url'        => 'https://example.com/completed-page',
					'title'      => 'Completed Page',
					'is_mobile'  => true,
					'job_id'     => 'old-job-id',
					'status'     => 'completed',
					'score'      => '85',
					'report_url' => 'https://example.com/report',
					'is_blurred' => 0,
					'data'       => '{"status":"complete"}',
				],
			],
			'mock_http'      => true,
		],
		'expected' => [
			'is_error'           => false,
			'success'            => true,
			'status'             => 'triggered',
			'error'              => '',
			'hook_fired'         => true,
			'stale_fields_cleared' => true,
		],
	],
];
