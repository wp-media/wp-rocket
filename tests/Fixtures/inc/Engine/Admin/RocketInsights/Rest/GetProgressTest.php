<?php

return [
	'testShouldGetResultsSuccessfully' => [
		'config' => [
			'database_entries' => [
				[
					'id' => 1,
					'url' => 'http://example.org/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete","data":{"data":{"performance_score":85}}}',
				],
				[
					'id' => 2,
					'url' => 'http://example.org/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'in-progress',
					'score' => 0,
					'data' => '{"status":"in-progress"}',
				],
			],
			'get_data' => [
				'ids' => [ 1, 2 ],
			],
		],
		'expected' => [
			'code' => 200,
			'success' => true,
			'results_count' => 2,
			'has_global_score_data' => true,
			'results_have_html' => true,
		],
	],
	'testShouldGetSingleResultSuccessfully' => [
		'config' => [
			'database_entries' => [
				[
					'id' => 1,
					'url' => 'http://example.org/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 90,
					'data' => '{"status":"complete","data":{"data":{"performance_score":90}}}',
				],
			],
			'get_data' => [
				'ids' => [ 1 ],
			],
		],
		'expected' => [
			'code' => 200,
			'success' => true,
			'results_count' => 1,
			'has_global_score_data' => true,
			'results_have_html' => true,
		],
	],
	'testShouldFailWithMissingIds' => [
		'config' => [
			'get_data' => [],
		],
		'expected' => [
			'code' => 400,
			'error_message' => 'Missing parameter(s): ids',
		],
	],
	'testShouldFailWithEmptyIds' => [
		'config' => [
			'get_data' => [
				'ids' => [],
			],
		],
		'expected' => [
			'code' => 400,
			'error_message' => 'ids empty',
		],
	],
	'testShouldFailWithInvalidIdValues' => [
		'config' => [
			'get_data' => [
				'ids' => [ 0, -1 ], // These will be filtered out by array_filter
			],
		],
		'expected' => [
			'code' => 404,
			'error_message' => 'No rows found in DB for ids: -1',
		],
	],
	'testShouldFailWithNoResultsFound' => [
		'config' => [
			'database_entries' => [],
			'get_data' => [
				'ids' => [ 999, 1000 ],
			],
		],
		'expected' => [
			'code' => 404,
			'error_message' => 'No rows found in DB for ids: 999,1000',
		],
	],
	'testShouldGetPartialResults' => [
		'config' => [
			'database_entries' => [
				[
					'id' => 1,
					'url' => 'http://example.org/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 75,
					'data' => '{"status":"complete","data":{"data":{"performance_score":75}}}',
				],
			],
			'get_data' => [
				'ids' => [ 1, 999 ], // One exists, one doesn't
			],
		],
		'expected' => [
			'code' => 200,
			'success' => true,
			'results_count' => 1, // Only the existing one should be returned
			'has_global_score_data' => true,
			'results_have_html' => true,
		],
	],
];
