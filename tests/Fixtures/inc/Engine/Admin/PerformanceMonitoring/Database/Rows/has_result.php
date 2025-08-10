<?php

return [
	'test_data' => [
		'completedWithData' => [
			'config' => [
				'id' => 1,
				'url' => 'https://example.com/test-page',
				'is_mobile' => false,
				'test_id' => 'test_123',
				'status' => 'completed',
				'data' => '{"status":"complete","data":{"data":{"gtmetrix_id":"abc123","report_url":"https://gtmetrix.com/reports/example.com/abc123","performance_score":95,"structure_score":90,"largest_contentful_paint":1.2,"total_blocking_time":0.05,"cumulative_layout_shift":0.01,"first_contentful_paint":0.8,"time_to_interactive":1.5,"speed_index":1.0,"fully_loaded_time":2.5,"page_size":1024,"requests":45},"server_name":"Vancouver, Canada","region_name":"North America","browser_name":"Chrome","platform":"Desktop"}}',
				'error_message' => '',
				'modified' => time(),
				'last_accessed' => time(),
			],
			'expected' => true,
		],
		'completedWithoutData' => [
			'config' => [
				'id' => 2,
				'url' => 'https://example.com/test-page',
				'is_mobile' => false,
				'test_id' => 'test_456',
				'status' => 'completed',
				'data' => '',
				'error_message' => '',
				'modified' => time(),
				'last_accessed' => time(),
			],
			'expected' => false,
		],
		'pendingWithData' => [
			'config' => [
				'id' => 3,
				'url' => 'https://example.com/test-page',
				'is_mobile' => false,
				'test_id' => 'test_789',
				'status' => 'pending',
				'data' => '{"status":"pending","data":{"server_name":"London, UK","region_name":"Europe","browser_name":"Chrome","platform":"Desktop"}}',
				'error_message' => '',
				'modified' => time(),
				'last_accessed' => time(),
			],
			'expected' => false,
		],
		'inProgressWithData' => [
			'config' => [
				'id' => 4,
				'url' => 'https://example.com/test-page',
				'is_mobile' => true,
				'test_id' => 'test_101',
				'status' => 'in-progress',
				'data' => '{"status":"running","data":{"server_name":"Sydney, Australia","region_name":"Asia Pacific","browser_name":"Chrome","platform":"Mobile"}}',
				'error_message' => '',
				'modified' => time(),
				'last_accessed' => time(),
			],
			'expected' => false,
		],
		'failedWithData' => [
			'config' => [
				'id' => 5,
				'url' => 'https://example.com/test-page',
				'is_mobile' => false,
				'test_id' => 'test_202',
				'status' => 'failed',
				'data' => '{"status":"error","error":"Request timeout","data":{"server_name":"New York, USA","region_name":"North America","browser_name":"Chrome","platform":"Desktop"}}',
				'error_message' => 'Test timeout error',
				'modified' => time(),
				'last_accessed' => time(),
			],
			'expected' => false,
		],
		'mobileTypeCasting' => [
			'config' => [
				'id' => 6,
				'url' => 'https://example.com/mobile-page',
				'is_mobile' => 1, // Will be cast to bool
				'test_id' => 'test_mobile',
				'status' => 'completed',
				'data' => '{"status":"complete","data":{"data":{"gtmetrix_id":"mobile123","report_url":"https://gtmetrix.com/reports/example.com/mobile123","performance_score":88,"structure_score":85,"largest_contentful_paint":2.1,"total_blocking_time":0.15,"cumulative_layout_shift":0.05,"first_contentful_paint":1.2,"time_to_interactive":2.8,"speed_index":1.8,"fully_loaded_time":4.2,"page_size":892,"requests":38},"server_name":"Tokyo, Japan","region_name":"Asia Pacific","browser_name":"Chrome","platform":"Mobile"}}',
				'error_message' => '',
				'modified' => time(),
				'last_accessed' => time(),
			],
			'expected' => true,
		],
	],
];
