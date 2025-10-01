<?php

return [
	'testShouldDeleteRowSuccessfully' => [
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
			'valid_nonce' => true,
			'get_data' => [
				'id' => 1,
			],
		],
		'expected' => [
			'hook_fired' => true,
			'hook_fired_id' => 1,
			'database_entries_after' => 1, // One item should remain
			'item_deleted_id' => 1,
			'item_exists_id' => 2,
		],
	],
	'testShouldDeleteOnlySpecifiedRow' => [
		'config' => [
			'database_entries' => [
				[
					'id' => 5,
					'url' => 'http://example.org/page5',
					'is_mobile' => false,
					'job_id' => 'test_555',
					'status' => 'completed',
					'score' => 95,
					'data' => '{"status":"complete","data":{"data":{"performance_score":95}}}',
				],
			],
			'valid_nonce' => true,
			'get_data' => [
				'id' => 5,
			],
		],
		'expected' => [
			'hook_fired' => true,
			'hook_fired_id' => 5,
			'database_entries_after' => 0,
			'item_deleted_id' => 5,
		],
	],
	'testShouldNotDeleteWithInvalidId' => [
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
			'valid_nonce' => true,
			'get_data' => [
				'id' => 0, // Invalid ID
			],
		],
		'expected' => [
			'hook_fired' => false,
			'database_entries_after' => 1, // Item should still exist
			'item_exists_id' => 1,
		],
	],
	'testShouldNotDeleteWithMissingId' => [
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
			'valid_nonce' => true,
			'get_data' => [
				// No ID provided
			],
		],
		'expected' => [
			'hook_fired' => false,
			'database_entries_after' => 1, // Item should still exist
			'item_exists_id' => 1,
		],
	],
	'testShouldNotDeleteWithNonExistentId' => [
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
			'valid_nonce' => true,
			'get_data' => [
				'id' => 999, // Non-existent ID
			],
		],
		'expected' => [
			'hook_fired' => true, // Hook still fires even if item doesn't exist
			'hook_fired_id' => 999,
			'database_entries_after' => 1, // Original item should still exist
			'item_exists_id' => 1,
		],
	],
	'testShouldFailWithInvalidNonce' => [
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
			'valid_nonce' => false,
			'get_data' => [
				'id' => 1,
				'_wpnonce' => 'invalid_nonce',
			],
		],
		'expected' => [
			'hook_fired' => false,
			'database_entries_after' => 1, // Item should still exist
			'item_exists_id' => 1,
		],
	],
	'testShouldFailWithMissingNonce' => [
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
			'valid_nonce' => false,
			'get_data' => [
				'id' => 1,
				// No nonce provided
			],
		],
		'expected' => [
			'hook_fired' => false,
			'database_entries_after' => 1, // Item should still exist
			'item_exists_id' => 1,
		],
	],
	'testShouldFailWhenNotAllowed' => [
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
			'valid_nonce' => true,
			'get_data' => [
				'id' => 1,
			],
			'filters' => [
				'rocket_performance_monitoring_enabled' => '__return_false',
			],
		],
		'expected' => [
			'hook_fired' => false,
			'database_entries_after' => 1, // Item should still exist
			'item_exists_id' => 1,
		],
	],
];