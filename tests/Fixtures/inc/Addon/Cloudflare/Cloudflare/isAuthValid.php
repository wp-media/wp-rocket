<?php

return [
	'shouldReturnWPErrorWhenEmptyZoneID' => [
		'config' => [
			'zone_id' => '',
			'response' => [],
		],
		'expected' => 'error',
	],
	'shouldReturnWPErrorWhenEmptyResult' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'result' => [],
			],
		],
		'expected' => 'error',
	],
	'shouldReturnWPErrorWhenNotFound' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'result' => [
					'name' => 'test.com',
				],
			],
		],
		'expected' => 'error',
	],
	'shouldReturnTrueWhenFound' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'result' => (object) [
					'name' => 'example.org',
				],
			],
		],
		'expected' => true,
	],
	'shouldReturnWPErrorWhenCredentialsException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => 'credentials_exception',
		],
		'expected' => 'error',
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => 'exception',
		],
		'expected' => 'error',
	],
];
