<?php
return [
	'testFailRequestShouldReturnError' => [
		'config'   => [
			'url'            => 'https://example.com',
			'url_with_slash' => 'https://example.com/',
			'api_url'        => 'https://api.example.com',
			'email'          => 'example@email.com',
			'key'            => 'key',
			'response'       => [
				'code'    => 400,
				'message' => 'message',
				'body'    => 'body',
			],
			'errors_count'   => 1,
			'request_uri'    => 'https://api.example.comperformance/',
			'request_body'   => [
				'credentials' => [
					'wpr_email' => 'example@email.com',
					'wpr_key'   => 'key',
				],
				'email'       => 'example@email.com',
				'key'         => 'key',
				'url'         => 'https://example.com/',
				'is_priority' => false,
			],
			'args'           => [
				'body'    => json_encode(
					[
						'credentials' => [
							'wpr_email' => 'example@email.com',
							'wpr_key'   => 'key',
						],
						'email'       => 'example@email.com',
						'key'         => 'key',
						'url'         => 'https://example.com/',
						'is_priority' => false,
					]
				),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'method'  => 'POST',
			],
			'options'        => [],
			'is_succeed'     => false,
			'code'           => 400,
			'message'        => 'message',
			'body'           => '',
		],
		'expected' => [
			'code'    => 400,
			'message' => 'message',
		],
	],
	'testSucceedRequestShouldReturnBody' => [
		'config'   => [
			'url'            => 'https://example.com',
			'url_with_slash' => 'https://example.com/',
			'api_url'        => 'https://api.example.com',
			'email'          => 'example@email.com',
			'key'            => 'key',
			'response'       => [
				'code'    => 200,
				'message' => 'message',
				'body'    => 'body',
			],
			'errors_count'   => 1,
			'request_uri'    => 'https://api.example.comperformance/',
			'request_body'   => [
				'credentials' => [
					'wpr_email' => 'example@email.com',
					'wpr_key'   => 'key',
				],
				'email'       => 'example@email.com',
				'key'         => 'key',
				'url'         => 'https://example.com/',
				'is_priority' => false,
			],
			'args'           => [
				'body'    => json_encode(
					[
						'credentials' => [
							'wpr_email' => 'example@email.com',
							'wpr_key'   => 'key',
						],
						'email'       => 'example@email.com',
						'key'         => 'key',
						'url'         => 'https://example.com/',
						'is_priority' => false,
					]
				),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'method'  => 'POST',
			],
			'options'        => [],
			'is_succeed'     => true,
			'code'           => 200,
			'message'        => 'message',
			'body'           => json_encode(
				[
					'uuid'    => 'test-uuid-123',
					'message' => 'Test initiated',
				]
			),
		],
		'expected' => [
			'uuid'    => 'test-uuid-123',
			'message' => 'Test initiated',
			'code'    => 200,
		],
	],
	'testSucceedTrailingSlashRequestShouldReturnBody' => [
		'config'   => [
			'url'            => 'https://example.com/test',
			'url_with_slash' => 'https://example.com/test/',
			'api_url'        => 'https://api.example.com',
			'email'          => 'example@email.com',
			'key'            => 'key',
			'response'       => [
				'code'    => 200,
				'message' => 'message',
				'body'    => 'body',
			],
			'errors_count'   => 1,
			'request_uri'    => 'https://api.example.comperformance/',
			'request_body'   => [
				'credentials' => [
					'wpr_email' => 'example@email.com',
					'wpr_key'   => 'key',
				],
				'email'       => 'example@email.com',
				'key'         => 'key',
				'url'         => 'https://example.com/test/',
				'is_priority' => false,
			],
			'args'           => [
				'body'    => json_encode(
					[
						'credentials' => [
							'wpr_email' => 'example@email.com',
							'wpr_key'   => 'key',
						],
						'email'       => 'example@email.com',
						'key'         => 'key',
						'url'         => 'https://example.com/test/',
						'is_priority' => false,
					]
				),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'method'  => 'POST',
			],
			'options'        => [],
			'is_succeed'     => true,
			'code'           => 200,
			'message'        => 'message',
			'body'           => json_encode(
				[
					'uuid'    => 'test-uuid-456',
					'message' => 'Test initiated with trailing slash',
				]
			),
		],
		'expected' => [
			'uuid'    => 'test-uuid-456',
			'message' => 'Test initiated with trailing slash',
			'code'    => 200,
		],
	],
];
