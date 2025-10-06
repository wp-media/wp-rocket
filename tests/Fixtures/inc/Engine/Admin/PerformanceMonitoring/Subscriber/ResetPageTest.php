<?php

return [
	'testShouldResetPageSuccessfully' => [
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
			],
			'post_data' => [
				'id' => 1,
			],
		],
		'expected' => [
			'success' => true,
			'hook_fired' => true,
			'hook_fired_id' => 1,
			'response_keys' => [ 'id', 'html', 'global_score_data' ],
			'response_data' => [
				'id' => 1,
			],
		],
	],
	'testShouldResetMultiplePages' => [
		'config' => [
			'database_entries' => [
				[
					'id' => 5,
					'url' => 'http://example.org/page5',
					'is_mobile' => true,
					'job_id' => 'test_555',
					'status' => 'failed',
					'score' => 0,
					'data' => '{"status":"failed","message":"Test failed"}',
				],
			],
			'post_data' => [
				'id' => 5,
			],
		],
		'expected' => [
			'success' => true,
			'hook_fired' => true,
			'hook_fired_id' => 5,
			'response_keys' => [ 'id', 'html', 'global_score_data' ],
			'response_data' => [
				'id' => 5,
			],
		],
	],
	'testShouldFailWithMissingId' => [
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
			],
			'post_data' => [
				// No ID provided
			],
		],
		'expected' => [
			'success' => false,
			'hook_fired' => false,
			'error_message' => 'No ID was provided',
		],
	],
	'testShouldFailWithInvalidId' => [
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
			],
			'post_data' => [
				'id' => 0, // Invalid ID
			],
		],
		'expected' => [
			'success' => false,
			'hook_fired' => false,
			'error_message' => 'No ID was provided',
		],
	],
	'testShouldFailWithNonExistentId' => [
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
			],
			'post_data' => [
				'id' => 999, // Non-existent ID
			],
		],
		'expected' => [
			'success' => false,
			'hook_fired' => false,
			'error_message' => 'Not valid ID',
		],
	],
	'testShouldFailWithStringId' => [
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
			],
			'post_data' => [
				'id' => 'invalid_string', // Will be converted to 0 by intval()
			],
		],
		'expected' => [
			'success' => false,
			'hook_fired' => false,
			'error_message' => 'No ID was provided',
		],
	],
	'testShouldFailWithNegativeId' => [
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
			],
			'post_data' => [
				'id' => -5, // Negative ID
			],
		],
		'expected' => [
			'success' => false,
			'hook_fired' => false,
			'error_message' => 'Not valid ID',
		],
	],
	'testShouldReturnCanAddPagesFlag' => [
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
			],
			'post_data' => [
				'id' => 1,
			],
		],
		'expected' => [
			'success' => true,
			'hook_fired' => true,
			'hook_fired_id' => 1,
			'response_keys' => [ 'id', 'html', 'global_score_data', 'remaining_urls', 'has_credit', 'can_add_pages' ],
			'response_data' => [
				'id' => 1,
			],
		],
	],
];