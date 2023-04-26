<?php

return [
	'shouldReturnValueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 'aggressive',
			'response' => (object) [
				'succcess' => true,
			],
			'request_error' => false,
		],
		'expected' => 'aggressive',
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
