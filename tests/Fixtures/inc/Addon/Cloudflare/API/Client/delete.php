<?php

return [
	'testShouldReturnEmptyCredentialsWPError' => [
		'config' => [
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
			'valid_credentials' => true,
			'valid_error' => false,
			'path' => '',
			'data' => [],
			'response' => new WP_Error( '404', 'message' ),
			'request_error' => true,
		],
		'expected' => [
			'error_code' => '404',
			'result' => 'error',
		],
	],
	'testShouldReturnEmptyResponseWPError' => [
		'config' => [
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
