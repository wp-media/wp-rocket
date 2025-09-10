<?php

return [
	'testShouldReturnCompleteStatus' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete"}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'completed',
					'score' => 92,
					'data' => '{"status":"complete"}',
				],
			],
		],
		'expected' => [
			'status' => 'complete',
		]
	],
	'testShouldReturnInProgressStatus' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete"}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'in-progress',
					'score' => 0,
					'data' => '{"status":"in-progress"}',
				],
			],
		],
		'expected' => [
			'status' => 'in-progress',
		]
	],
	'testShouldReturnNoUrlStatus' => [
		'config' => [
			'items' => [],
		],
		'expected' => [
			'status' => 'no-url',
		]
	],
	'testShouldReturnInProgressWithPendingStatus' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'pending',
					'score' => 0,
					'data' => '{"status":"pending"}',
				],
			],
		],
		'expected' => [
			'status' => 'in-progress',
		]
	],
	'testShouldReturnInProgressWithToSubmitStatus' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'to-submit',
					'score' => 0,
					'data' => '{"status":"to-submit"}',
				],
			],
		],
		'expected' => [
			'status' => 'in-progress',
		]
	],
];