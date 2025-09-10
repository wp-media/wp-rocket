<?php

return [
	'testShouldReturnAverageGlobalScore' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 85,
					'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/abc123","performance_score":85,"structure_score":80,"largest_contentful_paint":2.5,"total_blocking_time":0.15,"cumulative_layout_shift":0.05,"first_contentful_paint":1.5,"time_to_interactive":3.0,"speed_index":2.0,"fully_loaded_time":4.0,"page_size":1024,"requests":50},"server_name":"New York, USA","region_name":"North America","browser_name":"Chrome","platform":"Desktop"}}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'completed',
					'score' => 92,
					'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/def456","performance_score":92,"structure_score":87,"largest_contentful_paint":1.8,"total_blocking_time":0.08,"cumulative_layout_shift":0.02,"first_contentful_paint":1.1,"time_to_interactive":2.2,"speed_index":1.5,"fully_loaded_time":3.1,"page_size":956,"requests":42},"server_name":"London, UK","region_name":"Europe","browser_name":"Chrome","platform":"Mobile"}}',
				],
				[
					'url' => 'https://example.com/page3',
					'is_mobile' => false,
					'job_id' => 'test_789',
					'status' => 'completed',
					'score' => 78,
					'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/ghi789","performance_score":78,"structure_score":82,"largest_contentful_paint":3.2,"total_blocking_time":0.25,"cumulative_layout_shift":0.08,"first_contentful_paint":1.1,"time_to_interactive":2.2,"speed_index":1.5,"fully_loaded_time":3.1,"page_size":956,"requests":42},"server_name":"London, UK","region_name":"Europe","browser_name":"Chrome","platform":"Mobile"}}',
				],
			],
		],
		'expected' => [
			'transient' => 
			[
				'score' => 85, // Average of 85, 92, and 78
				'pages_num' => 3,
				'status' => 'complete',
			]			
		]
	],
	'testShouldReturnZeroWhenNoCompletedScores' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'in-progress',
					'score' => 0,
					'data' => '{"status":"in-progress"}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'failed',
					'score' => 0,
					'data' => '{"status":"failed","error":"Timeout"}',
				],
			],
		],
		'expected' => [
			'transient' => 
			[
				'score' => 0,
				'pages_num' => 2,
				'status' => 'in-progress',
			]			
		]
	],
	'testShouldReturnZeroWhenNoItems' => [
		'config' => [
			'items' => [],
		],
		'expected' => [
			'transient' => 
			[
				'score' => 0,
				'pages_num' => 0,
				'status' => 'no-url',
			]			
		]
	],
	'testShouldIgnoreZeroScores' => [
		'config' => [
			'items' => 
			[
				[
					'url' => 'https://example.com/page1',
					'is_mobile' => false,
					'job_id' => 'test_123',
					'status' => 'completed',
					'score' => 80,
					'data' => '{"status":"complete","data":{"data":{"performance_score":80}}}',
				],
				[
					'url' => 'https://example.com/page2',
					'is_mobile' => true,
					'job_id' => 'test_456',
					'status' => 'completed',
					'score' => 0, // This should be ignored
					'data' => '{"status":"complete","data":{"data":{"performance_score":0}}}',
				],
				[
					'url' => 'https://example.com/page3',
					'is_mobile' => false,
					'job_id' => 'test_789',
					'status' => 'completed',
					'score' => 90,
					'data' => '{"status":"complete","data":{"data":{"performance_score":90}}}',
				],
			],
		],
		'expected' => [
			'transient' => 
			[
				'score' => 85, // Average of 80 and 90 (ignoring 0)
				'pages_num' => 3,
				'status' => 'complete',
			]			
		]
	],
];