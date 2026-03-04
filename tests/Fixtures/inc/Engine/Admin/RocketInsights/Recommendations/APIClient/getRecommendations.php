<?php

return [
	'test_data' => [
		'shouldReturnRecommendationsSuccessfully' => [
			'config'   => [
				'params'       => [
					'email'        => 'user@example.com',
					'lcp'          => 3.2,
					'ttfb'         => 0.8,
					'cls'          => 0.15,
					'tbt'          => 350,
					'global_score' => 65,
					'language'     => 'en',
					'limit'        => 5,
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email'        => 'user@example.com',
						'lcp'          => 3.2,
						'ttfb'         => 0.8,
						'cls'          => 0.15,
						'tbt'          => 350,
						'global_score' => 65,
						'language'     => 'en',
						'limit'        => 5,
					],
					'timeout' => 15,
				],
				'response'     => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"recommendations":[{"option_slug":"delay_js","priority":10,"title":"Enable Delay JavaScript Execution","description":"Defer loading of non-critical JavaScript files.","learn_more_url":"https://docs.wp-rocket.me/article/1265","icon_slug":"javascript","lcp_impact":100,"ttfb_impact":null,"cls_impact":null,"tbt_impact":25}],"metadata":{"scores_analyzed":{"lcp":3.2,"ttfb":0.8,"cls":0.15,"tbt":350,"global_score":65},"enabled_options":[],"language":"en","total_recommendations":1}}',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'success' => true,
				'code'    => 200,
			],
		],

		'shouldReturnErrorWhenEmailMissing' => [
			'config'   => [
				'params'       => [
					'lcp' => 3.2,
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [],
				'response'     => null,
				'is_wp_error'  => false, // Not used because we return early
			],
			'expected' => [
				'success' => false,
				'code'    => 400,
			],
		],

		'shouldReturnErrorOnAPIFailure' => [
			'config'   => [
				'params'       => [
					'email' => 'user@example.com',
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email' => 'user@example.com',
					],
					'timeout' => 15,
				],
				'response'     => [
					'response' => [
						'code'    => 500,
						'message' => 'Internal Server Error',
					],
					'body'     => 'Internal Server Error',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'success' => false,
				'code'    => 500,
			],
		],

		'shouldReturnErrorOnInvalidJSON' => [
			'config'   => [
				'params'       => [
					'email' => 'user@example.com',
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email' => 'user@example.com',
					],
					'timeout' => 15,
				],
				'response'     => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => 'not valid json{',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'success' => false,
				'code'    => 400,
			],
		],

		'shouldReturnErrorOnInvalidStructure' => [
			'config'   => [
				'params'       => [
					'email' => 'user@example.com',
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email' => 'user@example.com',
					],
					'timeout' => 15,
				],
				'response'     => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"some_key":"some_value"}',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'success' => false,
				'code'    => 400,
			],
		],

		'shouldFilterNullAndEmptyParameters' => [
			'config'   => [
				'params'       => [
					'email'        => 'user@example.com',
					'lcp'          => null,
					'ttfb'         => '',
					'global_score' => 65,
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email'        => 'user@example.com',
						'global_score' => 65,
					],
					'timeout' => 15,
				],
				'response'     => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"recommendations":[],"metadata":{"scores_analyzed":{"global_score":65},"enabled_options":[],"language":"en","total_recommendations":0}}',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'success' => true,
				'code'    => 200,
			],
		],

		'shouldMergeCustomTimeout' => [
			'config'   => [
				'params'       => [
					'email' => 'user@example.com',
				],
				'custom_args'  => [
					'timeout' => 30,
				],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email' => 'user@example.com',
					],
					'timeout' => 30, // Custom timeout merged
				],
				'response'     => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"recommendations":[],"metadata":{"language":"en","total_recommendations":0}}',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'success' => true,
				'code'    => 200,
			],
		],

		'shouldHandleWPError' => [
			'config'   => [
				'params'       => [
					'email' => 'user@example.com',
				],
				'custom_args'  => [],
				'api_url'      => 'https://saas.wp-rocket.me/',
				'request_uri'  => 'https://saas.wp-rocket.me/recommendations/',
				'request_args' => [
					'method'  => 'GET',
					'body'    => [
						'email' => 'user@example.com',
					],
					'timeout' => 15,
				],
				'response'     => new \WP_Error( 'http_request_failed', 'A valid URL was not provided.' ),
				'is_wp_error'  => true,
			],
			'expected' => [
				'success' => false,
			],
		],
	],
];
