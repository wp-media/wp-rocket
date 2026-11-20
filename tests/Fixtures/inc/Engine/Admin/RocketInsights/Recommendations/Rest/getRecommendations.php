<?php

return [
	'test_data' => [
		'shouldReturnExpiredStatusWhenNoCache' => [
			'config'   => [
				'user_role'      => 'administrator',
				'transient_data' => false, // No cached data
			],
			'expected' => [
				'status'               => 'expired',
				'recommendations_count' => 0,
			],
		],

		'shouldReturnCompletedStatusWithRecommendations' => [
			'config'   => [
				'user_role'      => 'administrator',
				'transient_data' => [
					'status'          => 'completed',
					'recommendations' => [
						[
							'option_slug'   => 'delay_js',
							'title'         => 'Enable Delay JavaScript Execution',
							'description'   => 'Improve page load time by delaying JavaScript execution.',
							'impact'        => 'high',
							'current_value' => 0,
							'recommended_value' => 1,
						],
						[
							'option_slug'   => 'minify_css',
							'title'         => 'Enable CSS Minification',
							'description'   => 'Reduce CSS file size.',
							'impact'        => 'medium',
							'current_value' => 0,
							'recommended_value' => 1,
						],
					],
					'metadata'        => [
						'language'              => 'en',
						'total_recommendations' => 2,
					],
					'timestamp'       => time(),
					'language'        => 'en',
				],
			],
			'expected' => [
				'status'               => 'completed',
				'recommendations_count' => 2,
			],
		],

		'shouldReturnLoadingStatus' => [
			'config'   => [
				'user_role'      => 'administrator',
				'transient_data' => [
					'status'          => 'loading',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => time(),
					'language'        => 'en',
				],
			],
			'expected' => [
				'status'               => 'loading',
				'recommendations_count' => 0,
			],
		],

		'shouldReturnNoPagesMessageWhenNoTrackedPagesAndCachedFailed' => [
			'config'   => [
				'user_role'           => 'administrator',
				'global_score_status' => 'no-url',
				'transient_data'      => [
					'status'          => 'failed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => time(),
					'language'        => 'en',
					'error'           => 'Recommendations unavailable',
				],
			],
			'expected' => [
				'status'       => 'failed',
				'has_no_pages' => true,
			],
		],

		'shouldReturnFailedStatusWithError' => [
			'config'   => [
				'user_role'      => 'administrator',
				'transient_data' => [
					'status'          => 'failed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => time(),
					'language'        => 'en',
					'error'           => 'API request failed',
				],
			],
			'expected' => [
				'status'    => 'failed',
				'has_error' => true,
			],
		],

		'shouldReturnErrorForUnauthorizedUser' => [
			'config'   => [
				'user_role'      => 'subscriber', // User without capability
				'transient_data' => [
					'status'          => 'completed',
					'recommendations' => [
						[
							'option_slug' => 'delay_js',
							'title'       => 'Enable Delay JavaScript Execution',
						],
					],
					'metadata'        => [],
					'timestamp'       => time(),
					'language'        => 'en',
				],
			],
			'expected' => [
				'is_error'   => true,
				'error_code' => 'rest_forbidden',
			],
		],

		'shouldReturnErrorForUnauthenticatedUser' => [
			'config'   => [
				'user_role'      => 'none', // No user logged in
				'transient_data' => [
					'status'          => 'completed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => time(),
					'language'        => 'en',
				],
			],
			'expected' => [
				'is_error'   => true,
				'error_code' => 'rest_forbidden',
			],
		],
	],
];
