<?php

return [
	'testShouldThrowUnauthenticatedException' => [
		'config' => [
			'valid_credentials' => false,
			'path' => '',
			'data' => [],
			'response' => [],
			'error' => '',
			'body' => '',
		],
		'expected' => 'unauthenticated',
	],
	'testShouldThrowCredentialsException' => [
		'config' => [
			'valid_credentials' => false,
			'path' => '',
			'data' => [],
			'response' => [],
			'error' => '',
			'body' => '',
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
			'body' => '',
		],
		'expected' => 'exception',
	],
	'testShouldThrowExceptionWhenEmptyData' => [
		'config' => [
			'valid_credentials' => true,
			'path' => '',
			'data' => [],
			'response' => [],
			'error' => false,
			'body' => '',
		],
		'expected' => 'exception',
	],
	'testShouldThrowUnauthorizedException' => [
		'config' => [
			'valid_credentials' => true,
			'path' => '',
			'data' => [],
			'response' => [],
			'error' => false,
			'body' => json_encode( (object) [
				'success' => false,
				'errors' => [
					(object) [
						'code' => 6003,
					],
				],
			] ),
		],
		'expected' => 'unauthorized',
	],
	'testShouldThrowExceptionWhenErrorInResponse' => [
		'config' => [
			'valid_credentials' => true,
			'path' => '',
			'data' => [],
			'response' => [],
			'error' => false,
			'body' => json_encode( (object) [
				'success' => false,
				'errors' => [
					(object) [
						'code' => 4000,
						'message' => 'error',
					],
				],
			] ),
		],
		'expected' => 'exception',
	],
	'testShouldReturnResult' => [
		'config' => [
			'valid_credentials' => true,
			'path' => '',
			'data' => [],
			'response' => [],
			'error' => false,
			'body' => json_encode( (object) [
				'success' => true,
				'result' => [],
			] ),
		],
		'expected' => [],
	],
];
