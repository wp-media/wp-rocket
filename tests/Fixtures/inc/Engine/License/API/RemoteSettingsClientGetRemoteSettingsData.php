<?php

$settings_data = (object) [ 'rocket_insights_display_post_column' => true ];
$success_body  = '{"success":1,"data":{"rocket_insights_display_post_column":true}}';
$success_data  = json_decode( $success_body );

return [
	'testShouldReturnCachedDataWhenTransientSet' => [
		'config'   => [
			'transient'        => $settings_data,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => false,
		],
		'expected' => $settings_data,
	],

	'testShouldReturnFalseWhenTimeoutActive' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => true,
			'timeout-duration' => false,
			'response'         => false,
		],
		'expected' => false,
	],

	'testShouldReturnFalseWhenWPError' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => new WP_Error( 'http_request_failed', 'error' ),
		],
		'expected' => false,
	],

	'testShouldReturnFalseWhenNot200' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => [
				'headers'  => [],
				'body'     => 'error 404',
				'response' => [
					'code' => 404,
				],
				'cookies'  => [],
				'filename' => '',
			],
		],
		'expected' => false,
	],

	'testShouldReturnFalseWhenNoBody' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => [
				'headers'  => [],
				'body'     => '',
				'response' => [
					'code' => 200,
				],
				'cookies'  => [],
				'filename' => '',
			],
		],
		'expected' => false,
	],

	'testShouldReturnFalseWhenResponseHasNoSuccessField' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => [
				'headers'  => [],
				'body'     => '{"rocket_insights_display_post_column":true}',
				'response' => [
					'code' => 200,
				],
				'cookies'  => [],
				'filename' => '',
			],
		],
		'expected' => false,
	],

	'testShouldReturnFalseWhenResponseHasNoDataField' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => [
				'headers'  => [],
				'body'     => '{"success":1}',
				'response' => [
					'code' => 200,
				],
				'cookies'  => [],
				'filename' => '',
			],
		],
		'expected' => false,
	],

	'testShouldReturnDataWhenSuccess' => [
		'config'   => [
			'transient'        => false,
			'timeout-active'   => false,
			'timeout-duration' => false,
			'response'         => [
				'headers'  => [],
				'body'     => $success_body,
				'response' => [
					'code' => 200,
				],
				'cookies'  => [],
				'filename' => '',
			],
		],
		'expected' => $success_data,
	],
];
