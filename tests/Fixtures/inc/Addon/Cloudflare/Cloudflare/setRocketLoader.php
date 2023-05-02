<?php

return [
	'shouldReturnValueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 'on',
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
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'value' => 'off',
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
