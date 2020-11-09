<?php

return [
	'testShouldReturnErrorWhenWrongKeyAndEmail' => [
		'params'   => [
			'key'   => false,
			'email' => false,
		],
		'referer'  => 'https://wp-rocket.me',
		'expected' => [
			'code' => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email, key',
			'data' => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
					'key'   => 'Invalid parameter.',
				],
			],
		],
	],
	'testShouldReturnErrorWhenWrongKey' => [
		'params'   => [
			'key'   => false,
			'email' => true,
		],
		'referer'  => 'https://wp-rocket.me',
		'expected' => [
			'code' => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): key',
			'data' => [
				'status' => 400,
				'params' => [
					'key' => 'Invalid parameter.',
				],
			],
		],
	],
	'testShouldReturnErrorWhenWrongEmail' => [
		'params'   => [
			'key'   => true,
			'email' => false,
		],
		'referer'  => 'https://wp-rocket.me',
		'expected' => [
			'code' => 'rest_invalid_param',
			'message' => 'Invalid parameter(s): email',
			'data' => [
				'status' => 400,
				'params' => [
					'email' => 'Invalid parameter.',
				],
			],
		],
	],
	'testShouldReturnEmptyDataWhenWrongReferer' => [
		'params'   => [
			'key'   => true,
			'email' => true,
		],
		'referer'  => 'https://google.com',
		'expected' => [
			'code'    => 'rest_invalid_referer',
			'message' => 'Invalid referer',
			'data'    => [
				'status' => 400,
			],
		],
	],
	'testShouldReturnSupportDataWhenValid' => [
		'params'   => [
			'key'   => true,
			'email' => true,
		],
		'referer'  => 'https://wp-rocket.me',
		'expected' => [
			'code'    => 'rest_support_data_success',
			'message' => 'Support data request successful',
			'data'    => [
				'status'  => 200,
				'content' => [
					'Website'                  => 'http://example.org',
					'WordPress Version'        => '5.5',
					'WP Rocket Version'        => '3.7.5',
					'Theme'                    => 'WordPress Default',
					'Plugins Enabled'          => '',
					'WP Rocket Active Options' => '',
				],
			],
		],
	],
];
