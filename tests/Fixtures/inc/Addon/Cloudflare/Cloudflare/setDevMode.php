<?php

return [
	'shouldReturnValueOnWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 1,
			'setting' => 'on',
			'response' => (object) [
				'succcess' => true,
			],
			'request_error' => false,
		],
		'expected' => 'on',
	],
	'shouldReturnValueOffWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 0,
			'setting' => 'off',
			'response' => (object) [
				'succcess' => true,
			],
			'request_error' => false,
		],
		'expected' => 'off',
	],
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'value' => 0,
			'setting' => 'off',
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
