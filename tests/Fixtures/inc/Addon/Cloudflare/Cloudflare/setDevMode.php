<?php

return [
	'shouldReturnValueOnWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 1,
			'setting' => 'on',
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => '',
				] ),
				'response' => '',
				'cookies' => [],
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
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => '',
				] ),
				'response' => '',
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => 'off',
	],
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'value' => 1,
			'setting' => 'on',
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
