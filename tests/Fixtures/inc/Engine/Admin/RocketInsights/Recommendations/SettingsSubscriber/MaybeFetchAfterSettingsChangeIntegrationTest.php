<?php

return [
	'test_data' => [
		'shouldNotTriggerFetchWhenStatusIsPendingIntegration' => [
			'config' => [
				'initial_recommendations' => null, // No recommendations yet.
				'global_score_data'       => [
					'status'          => 'completed',
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
				'old_options'             => [ 'minify_css' => 0 ],
				'new_options'             => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_trigger_fetch' => false,
			],
		],

		'shouldNotTriggerFetchWhenNoRelevantChangesIntegration' => [
			'config' => [
				'initial_recommendations' => [
					'status'          => 'completed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'metrics_hash'    => 'test_hash_123',
				],
				'global_score_data'       => [
					'status'          => 'completed',
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
				'old_options'             => [ 'cache_lifespan' => 10 ],
				'new_options'             => [ 'cache_lifespan' => 20 ],
			],
			'expected' => [
				'should_trigger_fetch' => false,
			],
		],

		'shouldTriggerFetchWhenStatusCompletedAndRelevantChangesIntegration' => [
			'config' => [
				'initial_recommendations' => [
					'status'          => 'completed',
					'recommendations' => [
						[
							'id'    => 'enable_minify_css',
							'title' => 'Enable CSS Minification',
						],
					],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'metrics_hash'    => 'old_hash_123',
				],
				'global_score_data'       => [
					'status'          => 'completed',
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
				'old_options'             => [ 'minify_css' => 0 ],
				'new_options'             => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_trigger_fetch' => true,
			],
		],

		'shouldTriggerFetchWhenStatusFailedAndRelevantChangesIntegration' => [
			'config' => [
				'initial_recommendations' => [
					'status'          => 'failed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'error'           => 'API error',
					'metrics_hash'    => 'old_hash_456',
				],
				'global_score_data'       => [
					'status'          => 'completed',
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 2.5,
						'ttfb' => 0.8,
						'cls'  => 0.1,
						'tbt'  => 200,
					],
				],
				'old_options'             => [ 'delay_js' => 0 ],
				'new_options'             => [ 'delay_js' => 1 ],
			],
			'expected' => [
				'should_trigger_fetch' => true,
			],
		],
	],
];
