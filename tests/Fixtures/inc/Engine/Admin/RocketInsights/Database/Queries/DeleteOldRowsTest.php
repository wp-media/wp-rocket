<?php

return [
	'test_data' => [
		'deleteFailedRows' => [
			'config' => [
				'items' => [
					[
						'url' => 'https://example.com/page1',
						'is_mobile' => false,
						'job_id' => 'test_123',
						'status' => 'failed',
						'error_message' => 'Test failed',
					],
					[
						'url' => 'https://example.com/page2',
						'is_mobile' => true,
						'job_id' => 'test_456',
						'status' => 'completed',
						'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/def456","performance_score":92,"structure_score":87,"largest_contentful_paint":1.8,"total_blocking_time":0.08,"cumulative_layout_shift":0.02,"first_contentful_paint":1.1,"time_to_interactive":2.2,"speed_index":1.5,"fully_loaded_time":3.1,"page_size":956,"requests":42},"server_name":"London, UK","region_name":"Europe","browser_name":"Chrome","platform":"Mobile"}}',
					],
					[
						'url' => 'https://example.com/page3',
						'is_mobile' => false,
						'job_id' => 'test_789',
						'status' => 'pending',
					],
				],
			],
			'expected' => [
				'deleted_count' => '>= 0',
				'remaining_count' => '<= 3',
				'no_failed_status' => true,
			],
		],
		'deleteOldAccessedRows' => [
			'config' => [
				'items' => [
					[
						'url' => 'https://example.com/old-page',
						'is_mobile' => false,
						'job_id' => 'test_old',
						'status' => 'completed',
						'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/old789","performance_score":78,"structure_score":82,"largest_contentful_paint":3.2,"total_blocking_time":0.25,"cumulative_layout_shift":0.08,"first_contentful_paint":1.9,"time_to_interactive":4.1,"speed_index":2.8,"fully_loaded_time":5.2,"page_size":1250,"requests":58},"server_name":"Frankfurt, Germany","region_name":"Europe","browser_name":"Chrome","platform":"Desktop"}}',
					],
					[
						'url' => 'https://example.com/new-page',
						'is_mobile' => false,
						'job_id' => 'test_new',
						'status' => 'completed',
						'data' => '{"status":"complete","data":{"data":{"report_url":"https://gtmetrix.com/reports/example.com/new123","performance_score":94,"structure_score":91,"largest_contentful_paint":1.1,"total_blocking_time":0.04,"cumulative_layout_shift":0.01,"first_contentful_paint":0.7,"time_to_interactive":1.3,"speed_index":0.9,"fully_loaded_time":2.1,"page_size":875,"requests":35},"server_name":"New York, USA","region_name":"North America","browser_name":"Chrome","platform":"Desktop"}}',
					],
				],
				'update_old_timestamp' => [
					'job_id' => 'test_old',
					'last_accessed' => '-4 months', // Older than cleanup interval
				],
			],
			'expected' => [
				'deleted_count' => '>= 0',
				'remaining_count' => '<= 2',
			],
		],
		'emptyTable' => [
			'config' => [
				'items' => [],
			],
			'expected' => [
				'deleted_count' => 0,
				'remaining_count' => 0,
			],
		],
		'noDbInterface' => [
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
