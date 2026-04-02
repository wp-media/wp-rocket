<?php

return [
	'test_data' => [
		'shouldReturnOnlyCompletedTests' => [
			'config'   => [
				'rows' => [
					[
						'url'         => 'https://example.com/page1',
						'status'      => 'completed',
						'metric_data' => '{"largest_contentful_paint":2500,"time_to_first_byte":800,"cumulative_layout_shift":0.1,"total_blocking_time":300}',
						'score'       => 85,
					],
					[
						'url'         => 'https://example.com/page2',
						'status'      => 'pending',
						'metric_data' => '{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
						'score'       => 0,
					],
					[
						'url'         => 'https://example.com/page3',
						'status'      => 'completed',
						'metric_data' => '{"largest_contentful_paint":2000,"time_to_first_byte":600,"cumulative_layout_shift":0.05,"total_blocking_time":200}',
						'score'       => 90,
					],
					[
						'url'         => 'https://example.com/page4',
						'status'      => 'failed',
						'metric_data' => '{}',
						'score'       => 0,
					],
				],
			],
			'expected' => [
				'count'         => 2, // Only completed tests
				'expected_keys' => [
					'largest_contentful_paint',
					'time_to_first_byte',
					'cumulative_layout_shift',
					'total_blocking_time',
				],
			],
		],

		'shouldReturnEmptyWhenNoCompletedTests' => [
			'config'   => [
				'rows' => [
					[
						'url'         => 'https://example.com/page1',
						'status'      => 'pending',
						'metric_data' => '{}',
						'score'       => 0,
					],
					[
						'url'         => 'https://example.com/page2',
						'status'      => 'failed',
						'metric_data' => '{}',
						'score'       => 0,
					],
				],
			],
			'expected' => [
				'count'         => 0,
				'expected_keys' => [],
			],
		],

		'shouldReturnAllCompletedTests' => [
			'config'   => [
				'rows' => [
					[
						'url'         => 'https://example.com/page1',
						'status'      => 'completed',
						'metric_data' => '{"largest_contentful_paint":2500,"time_to_first_byte":800,"cumulative_layout_shift":0.1,"total_blocking_time":300}',
						'score'       => 85,
					],
					[
						'url'         => 'https://example.com/page2',
						'status'      => 'completed',
						'metric_data' => '{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
						'score'       => 75,
					],
					[
						'url'         => 'https://example.com/page3',
						'status'      => 'completed',
						'metric_data' => '{"largest_contentful_paint":2000,"time_to_first_byte":600,"cumulative_layout_shift":0.05,"total_blocking_time":200}',
						'score'       => 95,
					],
				],
			],
			'expected' => [
				'count'         => 3,
				'expected_keys' => [
					'largest_contentful_paint',
					'time_to_first_byte',
					'cumulative_layout_shift',
					'total_blocking_time',
				],
			],
		],

		'shouldHandleBlurredCompletedTests' => [
			'config'   => [
				'rows' => [
					[
						'url'         => 'https://example.com/page1',
						'status'      => 'completed',
						'metric_data' => '{"largest_contentful_paint":2500,"time_to_first_byte":800,"cumulative_layout_shift":0.1,"total_blocking_time":300}',
						'score'       => 85,
					],
					[
						'url'         => 'https://example.com/page2',
						'status'      => 'blurred', // Blurred tests should NOT be included
						'metric_data' => '{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
						'score'       => 75,
					],
				],
			],
			'expected' => [
				'count'         => 1, // Only 'completed', not 'blurred'
				'expected_keys' => [
					'largest_contentful_paint',
					'time_to_first_byte',
					'cumulative_layout_shift',
					'total_blocking_time',
				],
			],
		],
	],
];
