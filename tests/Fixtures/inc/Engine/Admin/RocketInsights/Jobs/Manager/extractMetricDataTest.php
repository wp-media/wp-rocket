<?php

return [
	'test_data' => [
		'shouldExtractAllMetricsWhenAvailable' => [
			'config' => [
				'data' => [
					'largest_contentful_paint' => 2.5,
					'total_blocking_time'      => 300,
					'cumulative_layout_shift'  => 0.1,
					'time_to_first_byte'       => 600,
				],
			],
			'expected' => [
				'lcp'  => 2.5,
				'tbt'  => 300,
				'cls'  => 0.1,
				'ttfb' => 600,
			],
		],

		'shouldReturnNullWhenAllMetricsAreMissing' => [
			'config' => [
				'data' => [
					'performance_score' => 85,
					'report_url'        => 'https://example.com/report',
				],
			],
			'expected' => null,
		],

		'shouldExtractPartialMetricsWithNullValues' => [
			'config' => [
				'data' => [
					'largest_contentful_paint' => 3.2,
					'total_blocking_time'      => null,
					'cumulative_layout_shift'  => 0.05,
					'time_to_first_byte'       => null,
				],
			],
			'expected' => [
				'lcp'  => 3.2,
				'tbt'  => null,
				'cls'  => 0.05,
				'ttfb' => null,
			],
		],

		'shouldReturnNullWhenMetricsAreAllNull' => [
			'config' => [
				'data' => [
					'largest_contentful_paint' => null,
					'total_blocking_time'      => null,
					'cumulative_layout_shift'  => null,
					'time_to_first_byte'       => null,
				],
			],
			'expected' => null,
		],

		'shouldHandleZeroValues' => [
			'config' => [
				'data' => [
					'largest_contentful_paint' => 0,
					'total_blocking_time'      => 0,
					'cumulative_layout_shift'  => 0,
					'time_to_first_byte'       => 0,
				],
			],
			'expected' => null,
		],

		'shouldExtractSingleMetric' => [
			'config' => [
				'data' => [
					'largest_contentful_paint' => 1.8,
				],
			],
			'expected' => [
				'lcp'  => 1.8,
				'tbt'  => null,
				'cls'  => null,
				'ttfb' => null,
			],
		],
	],
];
