<?php
return [
	'test_data' => [
		'shouldReturnTrueWhenAllRequiredMetricsPresent' => [
			'config'   => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 3.2,
						'ttfb' => 0.8,
						'cls'  => 0.15,
						'tbt'  => 350,
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
						'ttfb' => 0.8,
						'cls'  => 0.15,
						'tbt'  => 350,
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
						'lcp' => 3.2,
						'cls' => 0.15,
						'tbt' => 350,
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
						'lcp'  => 3.2,
						'ttfb' => 0.8,
						'tbt'  => 350,
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
						'lcp'  => 3.2,
						'ttfb' => 0.8,
						'cls'  => 0.15,
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
						'lcp'          => 3.2,
						'ttfb'         => 0.8,
						'cls'          => 0.15,
						'tbt'          => 350,
						'global_score' => 75,
					],
				],
			],
			'expected' => true,
		],
	],
];
