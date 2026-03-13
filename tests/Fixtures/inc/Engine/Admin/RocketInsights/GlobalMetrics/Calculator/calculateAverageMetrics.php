<?php

return [
	'test_data' => [
		'shouldReturnNullWhenNoCompletedTests' => [
			'config'   => [
				'tests' => [],
			],
			'expected' => [
				'largest_contentful_paint' => null,
				'time_to_first_byte'       => null,
				'cumulative_layout_shift'  => null,
				'total_blocking_time'      => null,
			],
		],

		'shouldCalculateAverageForSingleTest' => [
			'config'   => [
				'tests' => [
					'{"largest_contentful_paint":2500,"time_to_first_byte":800,"cumulative_layout_shift":0.1,"total_blocking_time":300}',
				],
			],
			'expected' => [
				'largest_contentful_paint' => 2.5,  // 2500ms / 1000 = 2.5s
				'time_to_first_byte'       => 0.8,  // 800ms / 1000 = 0.8s
				'cumulative_layout_shift'  => 0.1,
				'total_blocking_time'      => 300,
			],
		],

		'shouldCalculateAverageForMultipleTests' => [
			'config'   => [
				'tests' => [
					'{"largest_contentful_paint":2000,"time_to_first_byte":500,"cumulative_layout_shift":0.05,"total_blocking_time":200}',
					'{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
					'{"largest_contentful_paint":2500,"time_to_first_byte":700,"cumulative_layout_shift":0.1,"total_blocking_time":300}',
				],
			],
			'expected' => [
				'largest_contentful_paint' => 2.5,   // (2000 + 3000 + 2500) / 3 / 1000 = 2.5s
				'time_to_first_byte'       => 0.733,  // (500 + 1000 + 700) / 3 / 1000 = 0.733s
				'cumulative_layout_shift'  => 0.1,   // (0.05 + 0.15 + 0.1) / 3 = 0.1
				'total_blocking_time'      => 300,   // round((200 + 400 + 300) / 3) = 300ms
			],
		],

		'shouldHandleInvalidJsonGracefully' => [
			'config'   => [
				'tests' => [
					'{"largest_contentful_paint":2000,"time_to_first_byte":500,"cumulative_layout_shift":0.05,"total_blocking_time":200}',
					'invalid json{',
					'{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
				],
			],
			'expected' => [
				'largest_contentful_paint' => 2.5,   // (2000 + 3000) / 2 / 1000 = 2.5s
				'time_to_first_byte'       => 0.75,  // (500 + 1000) / 2 / 1000 = 0.75s
				'cumulative_layout_shift'  => 0.1,   // (0.05 + 0.15) / 2 = 0.1
				'total_blocking_time'      => 300,   // round((200 + 400) / 2) = 300ms
			],
		],

		'shouldHandleMissingMetricsGracefully' => [
			'config'   => [
				'tests' => [
					'{"largest_contentful_paint":2000,"cumulative_layout_shift":0.05}',
					'{"time_to_first_byte":1000,"total_blocking_time":400}',
				],
			],
			'expected' => [
				'largest_contentful_paint' => 1.0,   // 2000 / 2 / 1000 = 1.0s (second test has 0)
				'time_to_first_byte'       => 0.5,   // 1000 / 2 / 1000 = 0.5s (first test has 0)
				'cumulative_layout_shift'  => 0.025, // 0.05 / 2 = 0.025
				'total_blocking_time'      => 200,   // round(400 / 2) = 200ms
			],
		],

		'shouldReturnNullWhenAllTestsHaveInvalidData' => [
			'config'   => [
				'tests' => [
					'invalid json{',
					'{}',
					'null',
				],
			],
			'expected' => [
				'largest_contentful_paint' => null,
				'time_to_first_byte'       => null,
				'cumulative_layout_shift'  => null,
				'total_blocking_time'      => null,
			],
		],
	],
];
