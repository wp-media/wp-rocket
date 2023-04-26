<?php

return [
	'shouldReturnTrueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'succcess' => true,
			],
			'request_error' => false,
		],
		'expected' => true,
	],
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
