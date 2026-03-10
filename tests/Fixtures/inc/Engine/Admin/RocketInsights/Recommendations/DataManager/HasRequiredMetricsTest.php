<?php

return [
	'test_data' => [
		'shouldReturnTrueWhenAllMetricsExist' => [
			'config' => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
			],
			'expected' => [
				'has_metrics' => true,
			],
		],

		'shouldReturnFalseWhenAverageMetricsNull' => [
			'config' => [
				'global_score_data' => [
					'score' => 75,
				],
			],
			'expected' => [
				'has_metrics' => false,
			],
		],

		'shouldReturnFalseWhenAverageMetricsEmpty' => [
			'config' => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [],
				],
			],
			'expected' => [
				'has_metrics' => false,
			],
		],

		'shouldReturnFalseWhenMissingLCP' => [
			'config' => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'ttfb' => 0.8,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
			],
			'expected' => [
				'has_metrics' => false,
			],
		],

		'shouldReturnFalseWhenMissingTTFB' => [
			'config' => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp' => 2.5,
						'cls' => 0.1,
						'tbt' => 200,
					],
				],
			],
			'expected' => [
				'has_metrics' => false,
			],
		],

		'shouldReturnFalseWhenMissingCLS' => [
			'config' => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'tbt'  => 200,
					],
				],
			],
			'expected' => [
				'has_metrics' => false,
			],
		],

		'shouldReturnFalseWhenMissingTBT' => [
			'config' => [
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'cls'  => 0.1,
					],
				],
			],
			'expected' => [
				'has_metrics' => false,
			],
		],
	],
];
