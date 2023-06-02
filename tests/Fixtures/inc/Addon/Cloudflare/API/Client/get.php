<?php

return [
	'testShouldReturnEmptyCredentialsWPError' => [
		'config' => [
			'email' => '',
			'api_key' => '',
			'valid_credentials' => new WP_Error( 'cloudflare_credentials_empty', '' ),
			'valid_error' => true,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => '',
				'response' => [],
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'error_code' => 'cloudflare_credentials_empty',
			'result' => 'error',
		],
	],
	'testShouldReturnIncorrectCredentialsWPError' => [
		'config' => [
			'email' => 'roger',
			'api_key' => 'test12345',
			'valid_credentials' => false,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => '',
				'response' => [],
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'error_code' => 'cloudflare_invalid_credentials',
			'result' => 'error',
		],
	],
	'testShouldReturnWPErrorWhenRequestError' => [
		'config' => [
			'email' => 'roger@wp-rocket.me',
			'api_key' => 'test12345',
			'valid_credentials' => true,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => new WP_Error( 'request_error', 'message' ),
			'request_error' => true,
		],
		'expected' => [
			'error_code' => 'request_error',
			'result' => 'error',
		],
	],
	'testShouldReturnEmptyResponseWPError' => [
		'config' => [
			'email' => 'roger@wp-rocket.me',
			'api_key' => 'test12345',
			'valid_credentials' => true,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => '',
				'response' => [],
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'error_code' => 'cloudflare_no_reply',
			'result' => 'error',
		],
	],
	'testShouldReturnWPErrorWhenIncorrectResponseCode' => [
		'config' => [
			'email' => 'roger@wp-rocket.me',
			'api_key' => 'test12345',
			'valid_credentials' => true,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => false,
					'errors' => [
						(object) [
							'code' => 6003,
						],
					],
				] ),
				'response' => [],
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'error_code' => 'cloudflare_incorrect_credentials',
			'result' => 'error',
		],
	],
	'testShouldReturnWPErrorWhenCFError' => [
		'config' => [
			'email' => 'roger@wp-rocket.me',
			'api_key' => 'test12345',
			'valid_credentials' => true,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => false,
					'errors' => [
						(object) [
							'code' => 4000,
							'message' => 'error',
						],
					],
				] ),
				'response' => [],
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'error_code' => 'cloudflare_request_error',
			'result' => 'error',
		],
	],
	'testShouldReturnResult' => [
		'config' => [
			'email' => 'roger@wp-rocket.me',
			'api_key' => 'test12345',
			'valid_credentials' => true,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => [],
				] ),
				'response' => [],
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'result' => [],
		],
	],
];
