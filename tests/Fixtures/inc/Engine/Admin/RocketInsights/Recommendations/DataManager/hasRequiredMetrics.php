<?php
return [
	'test_data' => [
		'shouldReturnTrueWhenAllRequiredMetricsPresent' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'largest_contentful_paint'  => 3.2,
						'time_to_first_byte' => 0.8,
						'cumulative_layout_shift'  => 0.15,
						'total_blocking_time'  => 350,
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
						'time_to_first_byte' => 0.8,
						'cumulative_layout_shift'  => 0.15,
						'total_blocking_time'  => 350,
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
						'largest_contentful_paint' => 3.2,
						'cumulative_layout_shift' => 0.15,
						'total_blocking_time' => 350,
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
						'largest_contentful_paint'  => 3.2,
						'time_to_first_byte' => 0.8,
						'total_blocking_time'  => 350,
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
						'largest_contentful_paint'  => 3.2,
						'time_to_first_byte' => 0.8,
						'cumulative_layout_shift'  => 0.15,
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
						'largest_contentful_paint'          => 3.2,
						'time_to_first_byte'         => 0.8,
						'cumulative_layout_shift'          => 0.15,
						'total_blocking_time'          => 350,
						'global_score' => 75,
					],
				],
			],
			'expected' => true,
		],
	],
];
