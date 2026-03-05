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
					'body'     => '{"recommendations":[{"option_slug":"delay_js","priority":10,"title":"Enable Delay JavaScript Execution"}],"metadata":{"language":"en","total_recommendations":1}}',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'is_error' => false,
				'code'     => 200,
			],
		],

		'shouldReturnWPErrorWhenEmailMissing' => [
			'config'   => [
				'params'       => [
					'lcp' => 3.2,
				],
				'custom_args'  => [],
				'api_url'      => '',
				'request_uri'  => '',
				'request_args' => [],
				'response'     => null,
				'is_wp_error'  => false,
			],
			'expected' => [
				'is_error'   => true,
				'error_code' => 'missing_email',
			],
		],

		'shouldReturnWPErrorOnAPIFailure' => [
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
				'is_error'   => true,
				'error_code' => 'api_request_failed',
			],
		],

		'shouldReturnWPErrorOnInvalidJSON' => [
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
				'is_error'   => true,
				'error_code' => 'invalid_json',
			],
		],

		'shouldReturnWPErrorOnInvalidStructure' => [
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
				'is_error'   => true,
				'error_code' => 'invalid_structure',
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
					'body'     => '{"recommendations":[],"metadata":{"language":"en","total_recommendations":0}}',
				],
				'is_wp_error'  => false,
			],
			'expected' => [
				'is_error' => false,
				'code'     => 200,
			],
		],
	],
];
