<?php

return [
	'test_data' => [
		'updateWithDefaultStatus' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page1',
					'is_mobile' => 0,
					'status' => 'pending',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
				],
				'test_id' => 'gtmetrix_123456',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateWithCustomStatus' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page2',
					'is_mobile' => 1,
					'status' => 'pending',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
				],
				'test_id' => 'gtmetrix_789012',
				'status' => 'processing',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateWithCompletedStatus' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page3',
					'is_mobile' => 0,
					'status' => 'running',
					'test_id' => 'old_test_id',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-3 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-3 hours' ) ),
				],
				'test_id' => 'gtmetrix_345678',
				'status' => 'completed',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateNonExistentRecord' => [
			'config' => [
				'db_id' => 99999,
				'test_id' => 'gtmetrix_nonexistent',
				'status' => 'running',
			],
			'expected' => [
				'result' => false,
			],
		],
	],
];
