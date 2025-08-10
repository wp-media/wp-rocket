<?php

return [
	'test_data' => [
		'truncateExistingTable' => [
			'config' => [
				'items' => [
					[
						'url' => 'https://example.com/page1',
						'is_mobile' => false,
						'test_id' => 'test_123',
						'status' => 'completed',
						'data' => '{"status":"complete","data":{"data":{"gtmetrix_id":"trunc123","report_url":"https://gtmetrix.com/reports/example.com/trunc123","performance_score":91,"structure_score":88,"largest_contentful_paint":1.6,"total_blocking_time":0.09,"cumulative_layout_shift":0.02,"first_contentful_paint":0.9,"time_to_interactive":1.8,"speed_index":1.2,"fully_loaded_time":2.8,"page_size":987,"requests":41},"server_name":"Toronto, Canada","region_name":"North America","browser_name":"Chrome","platform":"Desktop"}}',
					],
					[
						'url' => 'https://example.com/page2',
						'is_mobile' => true,
						'test_id' => 'test_456',
						'status' => 'pending',
					],
				],
			],
			'expected' => [
				'result' => true,
				'remaining_count' => 0,
			],
		],
		'nonExistentTable' => [
			'config' => [
				'items' => [],
				'uninstall_table' => true,
			],
			'expected' => [
				'result' => 'boolean',
			],
		],
		'getTableName' => [
			'config' => [
				'items' => [],
			],
			'expected' => [
				'table_name_contains' => 'wpr_performance_monitoring',
			],
		],
	],
];
