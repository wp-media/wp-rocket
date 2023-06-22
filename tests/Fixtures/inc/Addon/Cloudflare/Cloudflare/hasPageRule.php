<?php

return [
	'shouldReturnTrueWhenHasPageRule' => [
		'config' => [
			'zone_id' => '12345',
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => (object) [
						'actions' => [
							'id' => 'cache_everything',
						],
					],
				] ),
				'response' => '',
				'cookies' => [],
			],
			'request_error' => false,
			'action_value' => 'cache_everything',
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenNotHasPageRule' => [
		'config' => [
			'zone_id' => '12345',
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => (object) [
						'actions' => [
							'id' => 'browser_check',
						],
					],
				] ),
				'response' => '',
				'cookies' => [],
			],
			'action_value' => 'cache_everything',
			'request_error' => false,
		],
		'expected' => false,
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => new WP_Error( 'error' ),
			'action_value' => 'cache_everything',
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
