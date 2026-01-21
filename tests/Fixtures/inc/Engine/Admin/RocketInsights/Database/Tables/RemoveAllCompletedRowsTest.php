<?php

return [
	'test_data' => [
		'mixedStatusRows' => [
			'config' => [
				'items' => [
					[
						'url' => 'https://example.com/page1',
						'is_mobile' => false,
						'test_id' => 'test_123',
						'status' => 'completed',
						'data' => '{"status":"complete","data":{"data":{"gtmetrix_id":"abc123","report_url":"https://gtmetrix.com/reports/example.com/abc123","performance_score":95,"structure_score":90,"largest_contentful_paint":1.2,"total_blocking_time":0.05,"cumulative_layout_shift":0.01,"first_contentful_paint":0.8,"time_to_interactive":1.5,"speed_index":1.0,"fully_loaded_time":2.5,"page_size":1024,"requests":45},"server_name":"Vancouver, Canada","region_name":"North America","browser_name":"Chrome","platform":"Desktop"}}',
					],
					[
						'url' => 'https://example.com/page2',
						'is_mobile' => true,
						'test_id' => 'test_456',
						'status' => 'failed',
						'error_message' => 'Test error',
					],
					[
						'url' => 'https://example.com/page3',
						'is_mobile' => false,
						'test_id' => 'test_789',
						'status' => 'pending',
					],
					[
						'url' => 'https://example.com/page4',
						'is_mobile' => true,
						'test_id' => 'test_101',
						'status' => 'in-progress',
					],
				],
			],
			'expected' => [
				'remaining_count' => 2,
				'remaining_statuses' => ['pending', 'in-progress'],
			],
		],
		'allCompletedFailed' => [
			'config' => [
				'items' => [
					[
						'url' => 'https://example.com/page1',
						'is_mobile' => false,
						'test_id' => 'test_123',
						'status' => 'completed',
						'data' => '{"status":"complete","data":{"data":{"gtmetrix_id":"def456","report_url":"https://gtmetrix.com/reports/example.com/def456","performance_score":88,"structure_score":85,"largest_contentful_paint":1.8,"total_blocking_time":0.12,"cumulative_layout_shift":0.03,"first_contentful_paint":1.0,"time_to_interactive":2.1,"speed_index":1.4,"fully_loaded_time":3.2,"page_size":945,"requests":38},"server_name":"Sydney, Australia","region_name":"Asia Pacific","browser_name":"Chrome","platform":"Desktop"}}',
					],
					[
						'url' => 'https://example.com/page2',
						'is_mobile' => true,
						'test_id' => 'test_456',
						'status' => 'failed',
						'error_message' => 'Test error',
					],
				],
			],
			'expected' => [
				'remaining_count' => 0,
			],
		],
		'nothingToRemove' => [
			'config' => [
				'items' => [
					[
						'url' => 'https://example.com/page1',
						'is_mobile' => false,
						'test_id' => 'test_123',
						'status' => 'pending',
					],
					[
						'url' => 'https://example.com/page2',
						'is_mobile' => true,
						'test_id' => 'test_456',
						'status' => 'in-progress',
					],
				],
			],
			'expected' => [
				'remaining_count' => 2,
			],
		],
		'nonExistentTable' => [
			'config' => [
				'items' => [],
				'uninstall_table' => true,
			],
			'expected' => [
				'result' => 'false_or_zero',
			],
		],
	],
];
