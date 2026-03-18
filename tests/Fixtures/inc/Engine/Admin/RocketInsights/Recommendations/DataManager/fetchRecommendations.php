<?php
return [
	'test_data' => [
		'shouldFetchSuccessfully' => [
			'config'   => [
				'version'             => '3.20.5',
				'locale'              => 'en_US',
				'options'             => [
					'consumer_email' => 'user@example.com',
					'delay_js'       => 1,
					'lazyload'       => 1,
				],
				'global_score_data'   => [
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
				'formatted_metric' => [
					'largest_contentful_paint' => 0,
					'time_to_first_byte'      => 0,
					'cumulative_layout_shift' => 0,
					'total_blocking_time'     => 0,
				],
				'expected_params'     => [
					'email' => 'user@example.com',
				],
				'api_response'        => [
					'code' => 200,
					'data' => [
						'recommendations' => [
							[
								'option_slug' => 'minify_css',
								'title'       => 'Enable CSS Minification',
							],
						],
						'metadata'        => [
							'language'              => 'en',
							'total_recommendations' => 1,
						],
					],
				],
				'transient_set_times' => 2, // loading + completed
			],
			'expected' => [
				'result'       => true,
				'final_status' => 'completed',
			],
		],

		'shouldHandleWPError' => [
			'config'   => [
				'version'             => '3.20.5',
				'locale'              => 'en_US',
				'options'             => [
					'consumer_email' => 'user@example.com',
				],
				'global_score_data'   => [
					'score' => 75,
				],
				'expected_params'     => [
					'email' => 'user@example.com',
				],
				'api_response'        => new WP_Error( 'api_error', 'API request failed' ),
				'transient_set_times' => 2, // loading + failed
			],
			'expected' => [
				'result'       => false,
				'final_status' => 'failed',
			],
		],

		'shouldHandleUnexpectedResponseFormat' => [
			'config'   => [
				'version'             => '3.20.5',
				'locale'              => 'en_US',
				'options'             => [
					'consumer_email' => 'user@example.com',
				],
				'global_score_data'   => [
					'score' => 75,
				],
				'expected_params'     => [
					'email' => 'user@example.com',
				],
				'api_response'        => [
					'some_key' => 'some_value', // Missing 'code' and 'data'
				],
				'transient_set_times' => 2, // loading + failed
			],
			'expected' => [
				'result'       => false,
				'final_status' => 'failed',
			],
		],

		'shouldHandleNoAverageMetrics' => [
			'config'   => [
				'version'             => '3.20.5',
				'locale'              => 'fr_FR',
				'options'             => [
					'consumer_email' => 'user@example.com',
				],
				'global_score_data'   => [
					'score' => 80,
					// No average_metrics
				],
				'expected_params'     => [
					'email' => 'user@example.com',
				],
				'api_response'        => [
					'code' => 200,
					'data' => [
						'recommendations' => [],
						'metadata'        => [
							'language'              => 'fr',
							'total_recommendations' => 0,
						],
					],
				],
				'transient_set_times' => 2, // loading + completed
			],
			'expected' => [
				'result'       => true,
				'final_status' => 'completed',
			],
		],
	],
];
