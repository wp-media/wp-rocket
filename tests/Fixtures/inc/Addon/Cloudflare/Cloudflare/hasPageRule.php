<?php

return [
	'shouldReturnTrueWhenHasPageRule' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'actions' => [
					'id' => 'cache_everything',
				],
			],
			'request_error' => false,
			'action_value' => 'cache_everything',
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenNotHasPageRule' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'actions' => [
					'id' => 'browser_check',
				],
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
