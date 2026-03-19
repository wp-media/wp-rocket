<?php
return [
	'test_data' => [
		'shouldReturnTrueWhenAllRequiredMetricsPresent' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'largest_contentful_paint' =>
							array (
								'label' => 'LCP',
								'value' => 3200,
								'tooltip' => 'Time until the largest visible content element renders and the main content becomes visible.',
							),
						'total_blocking_time' =>
							array (
								'label' => 'TBT',
								'value' => 350,
								'tooltip' => 'Total time the main thread is blocked before the page becomes interactive during loading.',
							),
						'cumulative_layout_shift' =>
							array (
								'label' => 'CLS',
								'value' => 0.15,
								'tooltip' => 'Total amount of unexpected layout shifts during page loading, affecting visual stability.',
							),
						'time_to_first_byte' =>
							array (
								'label' => 'TTFB',
								'value' => 800,
								'tooltip' => 'Time from the request until the server responds, determining how soon the page starts loading.',
							),
					],
				],
			],
			'expected' => true,
		],

		'shouldReturnFalseWhenNoAverageMetrics' => [
			'config'   => [
				'global_score_data' => [
					'score' => 75,
				],
			],
			'expected' => false,
		],

		'shouldReturnFalseWhenAverageMetricsIsEmpty' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [],
				],
			],
			'expected' => false,
		],

		'shouldReturnFalseWhenLcpIsMissing' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'total_blocking_time' =>
							array (
								'label' => 'TBT',
								'value' => 350,
								'tooltip' => 'Total time the main thread is blocked before the page becomes interactive during loading.',
							),
						'cumulative_layout_shift' =>
							array (
								'label' => 'CLS',
								'value' => 0.15,
								'tooltip' => 'Total amount of unexpected layout shifts during page loading, affecting visual stability.',
							),
						'time_to_first_byte' =>
							array (
								'label' => 'TTFB',
								'value' => 800,
								'tooltip' => 'Time from the request until the server responds, determining how soon the page starts loading.',
							),
					],
				],
			],
			'expected' => false,
		],

		'shouldReturnFalseWhenTtfbIsMissing' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'largest_contentful_paint' =>
							array (
								'label' => 'LCP',
								'value' => 3200,
								'tooltip' => 'Time until the largest visible content element renders and the main content becomes visible.',
							),
						'total_blocking_time' =>
							array (
								'label' => 'TBT',
								'value' => 350,
								'tooltip' => 'Total time the main thread is blocked before the page becomes interactive during loading.',
							),
						'cumulative_layout_shift' =>
							array (
								'label' => 'CLS',
								'value' => 0.15,
								'tooltip' => 'Total amount of unexpected layout shifts during page loading, affecting visual stability.',
							),
					],
				],
			],
			'expected' => false,
		],

		'shouldReturnFalseWhenClsIsMissing' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'largest_contentful_paint' =>
							array (
								'label' => 'LCP',
								'value' => 3200,
								'tooltip' => 'Time until the largest visible content element renders and the main content becomes visible.',
							),
						'total_blocking_time' =>
							array (
								'label' => 'TBT',
								'value' => 350,
								'tooltip' => 'Total time the main thread is blocked before the page becomes interactive during loading.',
							),
						'time_to_first_byte' =>
							array (
								'label' => 'TTFB',
								'value' => 800,
								'tooltip' => 'Time from the request until the server responds, determining how soon the page starts loading.',
							),
					],
				],
			],
			'expected' => false,
		],

		'shouldReturnFalseWhenTbtIsMissing' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'largest_contentful_paint' =>
							array (
								'label' => 'LCP',
								'value' => 3200,
								'tooltip' => 'Time until the largest visible content element renders and the main content becomes visible.',
							),
						'cumulative_layout_shift' =>
							array (
								'label' => 'CLS',
								'value' => 0.15,
								'tooltip' => 'Total amount of unexpected layout shifts during page loading, affecting visual stability.',
							),
						'time_to_first_byte' =>
							array (
								'label' => 'TTFB',
								'value' => 800,
								'tooltip' => 'Time from the request until the server responds, determining how soon the page starts loading.',
							),
					],
				],
			],
			'expected' => false,
		],

		'shouldReturnTrueWhenExtraMetricsPresent' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'largest_contentful_paint' =>
							array (
								'label' => 'LCP',
								'value' => 3200,
								'tooltip' => 'Time until the largest visible content element renders and the main content becomes visible.',
							),
						'total_blocking_time' =>
							array (
								'label' => 'TBT',
								'value' => 350,
								'tooltip' => 'Total time the main thread is blocked before the page becomes interactive during loading.',
							),
						'cumulative_layout_shift' =>
							array (
								'label' => 'CLS',
								'value' => 0.15,
								'tooltip' => 'Total amount of unexpected layout shifts during page loading, affecting visual stability.',
							),
						'time_to_first_byte' =>
							array (
								'label' => 'TTFB',
								'value' => 800,
								'tooltip' => 'Time from the request until the server responds, determining how soon the page starts loading.',
							),
						'global_score' => 75,
					],
				],
			],
			'expected' => true,
		],
	],
];
