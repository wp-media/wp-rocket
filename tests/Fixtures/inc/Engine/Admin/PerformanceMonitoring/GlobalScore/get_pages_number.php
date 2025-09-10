<?php

return [
	'testShouldReturnTotalPagesCount' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/abc123","performance_score":85,"structure_score":80}}}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'in-progress',
					'score' => 0,
					'data' => '{"status":"in-progress"}',
				],
				[
					'url' => 'https://example.com/page3',
					'is_mobile' => false,
					'job_id' => 'test_789',
					'status' => 'failed',
					'score' => 0,
					'data' => '{"status":"failed","error":"Timeout"}',
				],
			],
		],
		'expected' => [
			'pages_num' => 3,
		]
	],
	'testShouldReturnZeroWhenNoPages' => [
		'config' => [
			'items' => [],
		],
		'expected' => [
			'pages_num' => 0,
		]
	],
];