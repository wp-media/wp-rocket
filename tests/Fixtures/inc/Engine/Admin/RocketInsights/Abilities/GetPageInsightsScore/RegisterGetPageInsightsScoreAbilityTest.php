<?php

return [
	'testShouldReturnWPErrorWhenNoPermissions' => [
		'config'   => [
			'has_permission' => false,
			'input'          => [ 'url' => 'https://example.com/page1' ],
			'items'          => [],
		],
		'expected' => [
			'is_error'     => true,
			'exists'       => false,
			'results_count' => 0,
		],
	],
	'testShouldReturnExistsFalseWhenPageNotInDB' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [ 'url' => 'https://example.com/not-monitored' ],
			'items'          => [
				[
					'url'       => 'https://example.com/other-page',
					'title'     => 'Other Page',
					'is_mobile' => false,
					'job_id'    => 'test_other',
					'status'    => 'completed',
					'score'     => 90,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":90}}}',
				],
			],
		],
		'expected' => [
			'is_error'     => false,
			'exists'       => false,
			'results_count' => 0,
		],
	],
	'testShouldReturnExistsTrueWhenPageInDB' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [ 'url' => 'https://example.com/page1' ],
			'items'          => [
				[
					'url'       => 'https://example.com/page1',
					'title'     => 'Page 1',
					'is_mobile' => false,
					'job_id'    => 'test_123',
					'status'    => 'completed',
					'score'     => 85,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":85}}}',
				],
			],
		],
		'expected' => [
			'is_error'      => false,
			'exists'        => true,
			'results_count' => 1,
		],
	],
	'testShouldReturnBothRowsWhenPageHasDesktopAndMobile' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [ 'url' => 'https://example.com/page1' ],
			'items'          => [
				[
					'url'       => 'https://example.com/page1',
					'title'     => 'Page 1',
					'is_mobile' => false,
					'job_id'    => 'test_desktop',
					'status'    => 'completed',
					'score'     => 85,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":85}}}',
				],
				[
					'url'       => 'https://example.com/page1',
					'title'     => 'Page 1',
					'is_mobile' => true,
					'job_id'    => 'test_mobile',
					'status'    => 'completed',
					'score'     => 75,
					'data'      => '{"status":"complete","data":{"data":{"performance_score":75}}}',
				],
			],
		],
		'expected' => [
			'is_error'      => false,
			'exists'        => true,
			'results_count' => 2,
		],
	],
];
