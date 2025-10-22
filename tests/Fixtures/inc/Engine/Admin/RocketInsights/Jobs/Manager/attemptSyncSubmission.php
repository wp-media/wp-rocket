<?php
return [
	'test_data' => [
		'shouldReturnSuccessWithUuid' => [
			'config' => [
				'url'                => 'https://example.com',
				'is_mobile'          => false,
				'additional_details' => [],
				'options'            => [ 'is_home' => false ],
				'timeout_args'       => [ 'timeout' => 10 ],
				'response'           => [
					'uuid' => 'test-uuid-123',
					'code' => 200,
				],
			],
			'expected' => [
				'is_error' => false,
				'uuid'     => 'test-uuid-123',
			],
		],
		'shouldReturnSuccessWithHomePagePriority' => [
			'config' => [
				'url'                => 'https://example.com',
				'is_mobile'          => false,
				'additional_details' => [ 'is_home' => true ],
				'options'            => [ 'is_home' => true ],
				'timeout_args'       => [ 'timeout' => 10 ],
				'response'           => [
					'uuid' => 'test-uuid-homepage',
					'code' => 200,
				],
			],
			'expected' => [
				'is_error' => false,
				'uuid'     => 'test-uuid-homepage',
			],
		],
		'shouldReturnWpErrorWhenNoUuidReturned' => [
			'config' => [
				'url'                => 'https://example.com',
				'is_mobile'          => false,
				'additional_details' => [],
				'options'            => [ 'is_home' => false ],
				'timeout_args'       => [ 'timeout' => 10 ],
				'response'           => [
					'code'    => 200,
					'message' => 'OK but no UUID',
				],
			],
			'expected' => [
				'is_error'      => true,
				'error_code'    => 'sync_submission_failed',
				'error_message' => 'No UUID returned',
			],
		],
		'shouldReturnWpErrorWhenApiReturnsError' => [
			'config' => [
				'url'                => 'https://example.com',
				'is_mobile'          => true,
				'additional_details' => [],
				'options'            => [ 'is_home' => false ],
				'timeout_args'       => [ 'timeout' => 10 ],
				'response'           => new \WP_Error( 'api_timeout', 'Request timed out' ),
			],
			'expected' => [
				'is_error'      => true,
				'error_code'    => 'api_timeout',
				'error_message' => 'Request timed out',
			],
		],
		'shouldReturnWpErrorWhenApiFails' => [
			'config' => [
				'url'                => 'https://example.com/test',
				'is_mobile'          => false,
				'additional_details' => [],
				'options'            => [ 'is_home' => false ],
				'timeout_args'       => [ 'timeout' => 10 ],
				'response'           => [
					'code'    => 400,
					'message' => 'Bad Request',
				],
			],
			'expected' => [
				'is_error'      => true,
				'error_code'    => 'sync_submission_failed',
				'error_message' => 'No UUID returned',
			],
		],
	],
];
