<?php

return [
	'test_data' => [
		'updateToCompleted' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page1',
					'is_mobile' => 0,
					'test_id' => 'gtmetrix_123456',
					'status' => 'running',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
				],
				'status' => 'completed',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateToFailed' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page2',
					'is_mobile' => 1,
					'test_id' => 'gtmetrix_789012',
					'status' => 'running',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
				],
				'status' => 'failed',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateToFailedWithErrorMessage' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page3',
					'is_mobile' => 0,
					'test_id' => 'gtmetrix_345678',
					'status' => 'running',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-3 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-3 hours' ) ),
				],
				'status' => 'failed',
				'error_message' => 'Test timed out after 5 minutes',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateExistingErrorMessage' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page4',
					'is_mobile' => 1,
					'test_id' => 'gtmetrix_456789',
					'status' => 'failed',
					'error_message' => 'Previous error message',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-4 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-4 hours' ) ),
				],
				'status' => 'failed',
				'error_message' => 'Updated error message',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateToPending' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page5',
					'is_mobile' => 0,
					'test_id' => 'gtmetrix_567890',
					'status' => 'failed',
					'error_message' => 'Previous error',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-5 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-5 hours' ) ),
				],
				'status' => 'pending',
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateNonExistentRecord' => [
			'config' => [
				'db_id' => 99999,
				'status' => 'completed',
			],
			'expected' => [
				'result' => false,
			],
		],
	],
];
