<?php

return [
	'test_data' => [
		'shouldFetchSuccessfully' => [
			'config'   => [
				'version'            => '3.20.5',
				'locale'             => 'en_US',
				'options'            => [
					'consumer_email' => 'user@example.com',
					'delay_js'       => 1,
					'lazyload'       => 1,
				],
				'global_score_data'  => [
					'score'           => 75,
					'average_metrics' => [
						'lcp'  => 3.2,
						'ttfb' => 0.8,
						'cls'  => 0.15,
						'tbt'  => 350,
					],
				],
				'expected_params'    => [
					'email' => 'user@example.com',
				],
				'api_response'       => [
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
				'result' => true,
			],
		],

		'shouldHandleAPIFailure' => [
			'config'   => [
				'version'            => '3.20.5',
				'locale'             => 'en_US',
				'options'            => [
					'consumer_email' => 'user@example.com',
				],
				'global_score_data'  => [
					'score' => 75,
				],
				'expected_params'    => [
					'email' => 'user@example.com',
				],
				'api_response'       => [
					'code'    => 500,
					'message' => 'API Error',
				],
				'transient_set_times' => 2, // loading + failed
			],
			'expected' => [
				'result' => false,
			],
		],
	],
];
