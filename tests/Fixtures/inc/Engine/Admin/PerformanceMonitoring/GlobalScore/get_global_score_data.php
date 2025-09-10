<?php

return [
	'testShouldReturnCompleteData' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete","data":{"data":{"performance_score":85}}}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'completed',
					'score' => 92,
					'data' => '{"status":"complete","data":{"data":{"performance_score":92}}}',
				],
				[
					'url' => 'https://example.com/page3',
					'is_mobile' => false,
					'job_id' => 'test_789',
					'status' => 'completed',
					'score' => 78,
					'data' => '{"status":"complete","data":{"data":{"performance_score":78}}}',
				],
			],
		],
		'expected' => [
			'data' => [
				'score' => 85, // Average of 85, 92, and 78
				'pages_num' => 3,
				'status' => 'complete',
			]
		]
	],
	'testShouldReturnInProgressData' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 90,
					'data' => '{"status":"complete","data":{"data":{"performance_score":90}}}',
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
			'data' => [
				'score' => 90, // Only completed scores are counted
				'pages_num' => 2,
				'status' => 'in-progress',
			]
		]
	],
	'testShouldReturnNoUrlData' => [
		'config' => [
			'items' => [],
		],
		'expected' => [
			'data' => [
				'score' => 0,
				'pages_num' => 0,
				'status' => 'no-url',
			]
		]
	],
];