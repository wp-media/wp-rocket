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
			'id' => 1,
		],
		'expected' => [
			'code' => 200,
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
			'id' => 5,
		],
		'expected' => [
			'code' => 200,
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
			'id' => null,
		],
		'expected' => [
			'code' => 404,
			'hook_fired' => false,
			'error_message' => 'No route was found matching the URL and request method.',
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
			'id' => 0, // Invalid ID
		],
		'expected' => [
			'code' => 400,
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
			'id' => 999, // Non-existent ID
		],
		'expected' => [
			'code' => 404,
			'hook_fired' => false,
			'error_message' => 'Item not found.',
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
			'id' => 'invalid_string', // Will be converted to 0 by intval()
		],
		'expected' => [
			'code' => 404,
			'hook_fired' => false,
			'error_message' => 'No route was found matching the URL and request method.',
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
			'id' => -5, // Negative ID
		],
		'expected' => [
			'code' => 404,
			'hook_fired' => false,
			'error_message' => 'No route was found matching the URL and request method.',
		],
	],
];
