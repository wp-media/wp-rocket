<?php

return [
	'shouldReturnWPErrorWhenEmptyZoneID' => [
		'config' => [
			'zone_id' => '',
			'response' => [],
			'request_error' => false,
		],
		'expected' => 'error',
	],
	'shouldReturnWPErrorWhenEmptyResult' => [
		'config' => [
			'zone_id' => '12345',
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
	'shouldReturnWPErrorWhenNotFound' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'name' => 'test.com',
			],
			'request_error' => false,
		],
		'expected' => 'error',
	],
	'shouldReturnTrueWhenFound' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'name' => 'example.org',
			],
			'request_error' => false,
		],
		'expected' => true,
	],
];
