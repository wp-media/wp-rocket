<?php
return [
	'test_data' => [
		'shouldReturnErrorWhenRequestFails' => [
			'config' => [
				'url'                           => 'https://example.com',
				'url_trailing'                  => 'https://example.com/',
				'url_filtered'                  => 'https://example.com/',
				'api_url'                       => 'https://api.example.com/',
				'email'                         => 'test@example.com',
				'key'                           => 'test-key-123',
				'options'                       => [],
				'custom_args'                   => [],
				'response'                      => [
					'response' => [
						'code'    => 400,
						'message' => 'Bad Request',
					],
				],
				'request_uri'                   => 'https://api.example.com/performance/',
				'request_body_with_credentials' => [
					'email'       => 'test@example.com',
					'key'         => 'test-key-123',
					'url'         => 'https://example.com/',
					'is_priority' => false,
					'credentials' => [
						'wpr_email' => 'test@example.com',
						'wpr_key'   => 'test-key-123',
					],
				],
				'request_body_json'             => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/","is_priority":false,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
				'args'                          => [
					'body'    => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/","is_priority":false,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
					'headers' => [
						'Content-Type' => 'application/json',
					],
					'method'  => 'POST',
				],
				'is_succeed'                    => false,
				'code'                          => 400,
				'message'                       => 'Bad Request',
			],
			'expected' => [
				'code'    => 400,
				'message' => 'Bad Request',
			],
		],
		'shouldReturnSuccessWithUuid' => [
			'config' => [
				'url'                           => 'https://example.com/test',
				'url_trailing'                  => 'https://example.com/test/',
				'url_filtered'                  => 'https://example.com/test/',
				'api_url'                       => 'https://api.example.com/',
				'email'                         => 'test@example.com',
				'key'                           => 'test-key-123',
				'options'                       => [],
				'custom_args'                   => [],
				'response'                      => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"uuid":"test-uuid-abc123"}',
				],
				'request_uri'                   => 'https://api.example.com/performance/',
				'request_body_with_credentials' => [
					'email'       => 'test@example.com',
					'key'         => 'test-key-123',
					'url'         => 'https://example.com/test/',
					'is_priority' => false,
					'credentials' => [
						'wpr_email' => 'test@example.com',
						'wpr_key'   => 'test-key-123',
					],
				],
				'request_body_json'             => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/test\/","is_priority":false,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
				'args'                          => [
					'body'    => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/test\/","is_priority":false,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
					'headers' => [
						'Content-Type' => 'application/json',
					],
					'method'  => 'POST',
				],
				'is_succeed'                    => true,
				'code'                          => 200,
				'message'                       => 'OK',
				'body'                          => '{"uuid":"test-uuid-abc123"}',
			],
			'expected' => [
				'uuid' => 'test-uuid-abc123',
				'code' => 200,
			],
		],
		'shouldHandleCustomTimeoutInArgs' => [
			'config' => [
				'url'                           => 'https://example.com',
				'url_trailing'                  => 'https://example.com/',
				'url_filtered'                  => 'https://example.com/',
				'api_url'                       => 'https://api.example.com/',
				'email'                         => 'test@example.com',
				'key'                           => 'test-key-123',
				'options'                       => [],
				'custom_args'                   => [ 'timeout' => 10 ],
				'response'                      => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"uuid":"test-uuid-with-timeout"}',
				],
				'request_uri'                   => 'https://api.example.com/performance/',
				'request_body_with_credentials' => [
					'email'       => 'test@example.com',
					'key'         => 'test-key-123',
					'url'         => 'https://example.com/',
					'is_priority' => false,
					'credentials' => [
						'wpr_email' => 'test@example.com',
						'wpr_key'   => 'test-key-123',
					],
				],
				'request_body_json'             => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/","is_priority":false,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
				'args'                          => [
					'body'    => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/","is_priority":false,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
					'headers' => [
						'Content-Type' => 'application/json',
					],
					'method'  => 'POST',
					'timeout' => 10,
				],
				'is_succeed'                    => true,
				'code'                          => 200,
				'message'                       => 'OK',
				'body'                          => '{"uuid":"test-uuid-with-timeout"}',
			],
			'expected' => [
				'uuid' => 'test-uuid-with-timeout',
				'code' => 200,
			],
		],
		'shouldHandleHomePagePriority' => [
			'config' => [
				'url'                           => 'https://example.com',
				'url_trailing'                  => 'https://example.com/',
				'url_filtered'                  => 'https://example.com/',
				'api_url'                       => 'https://api.example.com/',
				'email'                         => 'test@example.com',
				'key'                           => 'test-key-123',
				'options'                       => [ 'is_home' => true ],
				'custom_args'                   => [],
				'response'                      => [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => '{"uuid":"test-uuid-homepage"}',
				],
				'request_uri'                   => 'https://api.example.com/performance/',
				'request_body_with_credentials' => [
					'email'       => 'test@example.com',
					'key'         => 'test-key-123',
					'url'         => 'https://example.com/',
					'is_priority' => true,
					'credentials' => [
						'wpr_email' => 'test@example.com',
						'wpr_key'   => 'test-key-123',
					],
				],
				'request_body_json'             => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/","is_priority":true,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
				'args'                          => [
					'body'    => '{"email":"test@example.com","key":"test-key-123","url":"https:\/\/example.com\/","is_priority":true,"credentials":{"wpr_email":"test@example.com","wpr_key":"test-key-123"}}',
					'headers' => [
						'Content-Type' => 'application/json',
					],
					'method'  => 'POST',
				],
				'is_succeed'                    => true,
				'code'                          => 200,
				'message'                       => 'OK',
				'body'                          => '{"uuid":"test-uuid-homepage"}',
			],
			'expected' => [
				'uuid' => 'test-uuid-homepage',
				'code' => 200,
			],
		],
	],
];
