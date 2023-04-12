<?php

return [
	'testShouldThrowUnauthenticatedException' => [
		'config' => [
			'valid_credentials' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => '',
				'response' => [],
				'cookies' => [],
			],
			'error' => '',
		],
		'expected' => 'unauthenticated',
	],
	'testShouldThrowCredentialsException' => [
		'config' => [
			'valid_credentials' => false,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => '',
				'response' => [],
				'cookies' => [],
			],
			'error' => '',
		],
		'expected' => 'credentials',
	],
	'testShouldThrowExceptionWhenError' => [
		'config' => [
			'valid_credentials' => true,
			'path' => '',
			'data' => [],
			'response' => new WP_Error( '404', 'message' ),
			'error' => true,
		],
		'expected' => 'exception',
	],
	'testShouldThrowExceptionWhenEmptyData' => [
		'config' => [
			'valid_credentials' => true,
			'path' => '',
			'data' => [],
			'response' => [
				'headers' => [],
				'body' => '',
				'response' => [],
				'cookies' => [],
			],
			'error' => false,
		],
		'expected' => 'exception',
	],
	'testShouldThrowUnauthorizedException' => [
		'config' => [
			'valid_credentials' => true,
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
			'error' => false,
		],
		'expected' => 'unauthorized',
	],
	'testShouldThrowExceptionWhenErrorInResponse' => [
		'config' => [
			'valid_credentials' => true,
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
			'error' => false,
		],
		'expected' => 'exception',
	],
	'testShouldReturnResult' => [
		'config' => [
			'valid_credentials' => true,
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
			'error' => false,
		],
		'expected' => [],
	],
];
