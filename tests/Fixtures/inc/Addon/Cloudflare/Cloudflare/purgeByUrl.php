<?php

return [
	'shouldReturnTrueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'succcess' => true,
			],
			'request_error' => false,
			'urls' => [
				'about',
				'contact',
			],
		],
		'expected' => true,
	],
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'response' => new WP_Error( 'error' ),
			'urls' => [
				'about',
				'contact',
			],
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
