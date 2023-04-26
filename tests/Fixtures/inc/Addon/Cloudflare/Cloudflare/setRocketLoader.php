<?php

return [
	'shouldReturnValueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 'on',
			'response' => (object) [
				'succcess' => true,
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
