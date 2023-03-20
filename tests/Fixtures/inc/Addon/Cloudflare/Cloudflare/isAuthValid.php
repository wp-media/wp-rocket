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
			'response' => [],
		],
		'expected' => 'error',
	],
	'shouldReturnWPErrorWhenNotFound' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'name' => 'test.com',
			],
		],
		'expected' => 'error',
	],
	'shouldReturnTrueWhenFound' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'name' => 'example.org',
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
