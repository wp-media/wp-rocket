<?php

return [
	'test_data' => [
		'shouldNotFetchWhenStatusIsPending' => [
			'config' => [
				'status'                => 'pending',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldNotFetchWhenStatusIsLoading' => [
			'config' => [
				'status'                => 'loading',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldNotFetchWhenNoRelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => false,
				'old_options'           => [ 'cache_lifespan' => 10 ],
				'new_options'           => [ 'cache_lifespan' => 20 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldNotFetchWhenHashUnchanged' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldFetchWhenStatusCompletedAndRelevantChangesAndHashChanged' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		'shouldFetchWhenStatusFailedAndRelevantChanges' => [
			'config' => [
				'status'                => 'failed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'delay_js' => 0 ],
				'new_options'           => [ 'delay_js' => 1 ],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		'shouldDetectMultipleRelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [
					'minify_css' => 0,
					'minify_js'  => 0,
					'lazyload'   => 0,
				],
				'new_options'           => [
					'minify_css' => 1,
					'minify_js'  => 1,
					'lazyload'   => 1,
				],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		'shouldDetectMixedRelevantAndIrrelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [
					'minify_css'     => 0,
					'cache_lifespan' => 10,
				],
				'new_options'           => [
					'minify_css'     => 1,
					'cache_lifespan' => 20,
				],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		// Integration test scenarios.
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
					'timestamp'       => time(),
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
					'timestamp'       => time(),
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
					'timestamp'       => time(),
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
