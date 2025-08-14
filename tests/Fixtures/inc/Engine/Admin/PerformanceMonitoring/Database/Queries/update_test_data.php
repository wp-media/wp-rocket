<?php

return [
	'test_data' => [
		'updateWithCompleteData' => [
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
				'test_data' => [
					'status' => 'complete',
					'data' => [
						'data' => [
							'gtmetrix_id' => 'abc123',
							'report_url' => 'https://gtmetrix.com/reports/example.com/abc123',
							'performance_score' => 95,
							'structure_score' => 88,
							'largest_contentful_paint' => 1.2,
							'total_blocking_time' => 0.05,
							'cumulative_layout_shift' => 0.01,
							'first_contentful_paint' => 0.8,
							'time_to_interactive' => 1.5,
							'speed_index' => 1.0,
							'fully_loaded_time' => 2.3,
							'page_size' => 890,
							'requests' => 38,
						],
						'server_name' => 'London, UK',
						'region_name' => 'Europe',
						'browser_name' => 'Chrome',
						'platform' => 'Desktop',
					],
				],
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateMobileTestData' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page2',
					'is_mobile' => 1,
					'test_id' => 'gtmetrix_789012',
					'status' => 'running',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
				],
				'status' => 'completed',
				'test_data' => [
					'status' => 'complete',
					'data' => [
						'data' => [
							'gtmetrix_id' => 'def456',
							'report_url' => 'https://gtmetrix.com/reports/example.com/def456',
							'performance_score' => 92,
							'structure_score' => 87,
							'largest_contentful_paint' => 1.8,
							'total_blocking_time' => 0.08,
							'cumulative_layout_shift' => 0.02,
							'first_contentful_paint' => 1.1,
							'time_to_interactive' => 2.2,
							'speed_index' => 1.5,
							'fully_loaded_time' => 3.1,
							'page_size' => 956,
							'requests' => 42,
						],
						'server_name' => 'Frankfurt, Germany',
						'region_name' => 'Europe',
						'browser_name' => 'Chrome',
						'platform' => 'Mobile',
					],
				],
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateWithMinimalData' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page3',
					'is_mobile' => 0,
					'test_id' => 'gtmetrix_345678',
					'status' => 'running',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-30 minutes' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-30 minutes' ) ),
				],
				'status' => 'completed',
				'test_data' => [
					'status' => 'complete',
					'performance_score' => 85,
				],
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateWithEmptyArray' => [
			'config' => [
				'initial_record' => [
					'url' => 'https://example.com/page4',
					'is_mobile' => 1,
					'test_id' => 'gtmetrix_456789',
					'status' => 'running',
					'modified' => gmdate( 'Y-m-d H:i:s', strtotime( '-15 minutes' ) ),
					'last_accessed' => gmdate( 'Y-m-d H:i:s', strtotime( '-15 minutes' ) ),
				],
				'status' => 'completed',
				'test_data' => [],
			],
			'expected' => [
				'result' => true,
			],
		],
		'updateNonExistentRecord' => [
			'config' => [
				'db_id' => 99999,
				'status' => 'completed',
				'test_data' => [
					'status' => 'complete',
					'performance_score' => 90,
				],
			],
			'expected' => [
				'result' => false,
			],
		],
	],
];
