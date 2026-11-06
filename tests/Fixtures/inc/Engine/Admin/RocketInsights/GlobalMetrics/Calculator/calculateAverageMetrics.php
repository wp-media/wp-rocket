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
				'largest_contentful_paint' => 2500.0,
				'time_to_first_byte'       => 800.0,
				'cumulative_layout_shift'  => 0.1,
				'total_blocking_time'      => 300.0,
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
				'largest_contentful_paint' => 2500.0,   // (2000 + 3000 + 2500) / 3 = 2500
				'time_to_first_byte'       => (500 + 1000 + 700) / 3,  // (500 + 1000 + 700) / 3 = 733.333333333
				'cumulative_layout_shift'  => (0.05 + 0.15 + 0.1) / 3,   // (0.05 + 0.15 + 0.1) / 3 = 0.1
				'total_blocking_time'      => 300.0,   // (200 + 400 + 300) / 3 = 300ms
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
				'largest_contentful_paint' => 2500.0,   // (2000 + 3000) / 2
				'time_to_first_byte'       => 750.0,  // (500 + 1000) / 2
				'cumulative_layout_shift'  => (0.05 + 0.15) / 2,   // (0.05 + 0.15) / 2
				'total_blocking_time'      => 300.0,   // (200 + 400) / 2
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
				'largest_contentful_paint' => 1000.0,   // 2000 / 2
				'time_to_first_byte'       => 500.0,   // 1000 / 2
				'cumulative_layout_shift'  => 0.025, // 0.05 / 2
				'total_blocking_time'      => 200.0,   // 400 / 2 = 200ms
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

		'shouldReturnNullWhenAllTestsHaveNullMetricData' => [
			'config'   => [
				'tests' => [
					null,
					null,
					null,
				],
			],
			'expected' => [
				'largest_contentful_paint' => null,
				'time_to_first_byte'       => null,
				'cumulative_layout_shift'  => null,
				'total_blocking_time'      => null,
			],
		],

		'shouldReturnNullWhenOnlyOneTestAvailableWithNullMetricData' => [
			'config'   => [
				'tests' => [
					null,
				],
			],
			'expected' => [
				'largest_contentful_paint' => null,
				'time_to_first_byte'       => null,
				'cumulative_layout_shift'  => null,
				'total_blocking_time'      => null,
			],
		],

		'shouldHandleNullMetricsGracefullyWhenOnlyOneTestMetricDataIsNull' => [
			'config'   => [
				'tests' => [
					'{"largest_contentful_paint":2000,"time_to_first_byte":500,"cumulative_layout_shift":0.05,"total_blocking_time":200}',
					null,
					'{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
				],
			],
			'expected' => [
				'largest_contentful_paint' => 2500.0,   // (2000 + 3000) / 2
				'time_to_first_byte'       => 750.0,  // (500 + 1000) / 2
				'cumulative_layout_shift'  => (0.05 + 0.15) / 2,   // (0.05 + 0.15) / 2
				'total_blocking_time'      => 300.0,   // (200 + 400) / 2
			],
		],

		'shouldHandleEmptyMetricsGracefullyWhenOnlyOneTestMetricDataIsEmpty' => [
			'config'   => [
				'tests' => [
					'{"largest_contentful_paint":2000,"time_to_first_byte":500,"cumulative_layout_shift":0.05,"total_blocking_time":200}',
					'',
					'{"largest_contentful_paint":3000,"time_to_first_byte":1000,"cumulative_layout_shift":0.15,"total_blocking_time":400}',
				],
			],
			'expected' => [
				'largest_contentful_paint' => 2500.0,   // (2000 + 3000) / 2
				'time_to_first_byte'       => 750.0,  // (500 + 1000) / 2
				'cumulative_layout_shift'  => (0.05 + 0.15) / 2,   // (0.05 + 0.15) / 2
				'total_blocking_time'      => 300.0,   // (200 + 400) / 2
			],
		],

		'shouldReturnNullWhenOnlyOneTestAvailableWithEmptyMetricData' => [
			'config'   => [
				'tests' => [
					'',
				],
			],
			'expected' => [
				'largest_contentful_paint' => null,
				'time_to_first_byte'       => null,
				'cumulative_layout_shift'  => null,
				'total_blocking_time'      => null,
			],
		],

		'shouldReturnNullWhenAllTestsHaveEmptyMetricData' => [
			'config'   => [
				'tests' => [
					'',
					'',
					'',
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
