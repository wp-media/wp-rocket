<?php
return [
	'test_data' => [
		'shouldReturnTrueWhenNoCache' => [
			'config'   => [
				'transient_data'    => false,
				'calculate_hash'    => false,
				'global_score_data' => [],
				'options'           => [],
			],
			'expected' => true,
		],

		'shouldReturnTrueWhenCacheHasInvalidStructure' => [
			'config'   => [
				'transient_data' => [
					'recommendations' => [],
					// Missing 'status' and 'timestamp'
				],
				'calculate_hash'    => false,
				'global_score_data' => [],
				'options'           => [],
			],
			'expected' => true,
		],

		'shouldReturnTrueWhenHashChanged' => [
			'config'   => [
				'transient_data'    => [
					'status'       => 'completed',
					'timestamp'    => 1234567890,
					'metrics_hash' => 'old_hash_value',
				],
				'calculate_hash'    => true,
				'global_score_data' => [
					'score'           => 85,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.5,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
				'options'           => [
					'delay_js'  => 1,
					'lazyload'  => 1,
				],
			],
			'expected' => true,
		],

		'shouldReturnFalseWhenHashUnchanged' => [
			'config'   => [
				'transient_data'    => [
					'status'       => 'completed',
					'timestamp'    => 1234567890,
					'metrics_hash' => md5(
						(string) json_encode(
							[
								'score'           => 75,
								'average_metrics' => [
									'lcp'  => 3.2,
									'ttfb' => 0.8,
									'cls'  => 0.15,
									'tbt'  => 350,
								],
								'enabled_options' => [ 'delay_js', 'lazyload_images' ],
							]
						)
					),
				],
				'calculate_hash'    => true,
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 3.2,
						'ttfb' => 0.8,
						'cls'  => 0.15,
						'tbt'  => 350,
					],
				],
				'options'           => [
					'delay_js' => 1,
					'lazyload' => 1,
				],
			],
			'expected' => false,
		],

		'shouldReturnTrueWhenNoMetricsHashInCache' => [
			'config'   => [
				'transient_data'    => [
					'status'    => 'completed',
					'timestamp' => 1234567890,
					// No metrics_hash key
				],
				'calculate_hash'    => true,
				'global_score_data' => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 3.2,
						'ttfb' => 0.8,
						'cls'  => 0.15,
						'tbt'  => 350,
					],
				],
				'options'           => [],
			],
			'expected' => true,
		],
	],
];
