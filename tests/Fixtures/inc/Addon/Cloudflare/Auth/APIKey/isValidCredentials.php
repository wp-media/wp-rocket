<?php

return [
	'testShouldThrowExceptionWhenEmptyCredentials' => [
		'config' => [
			'email'   => '',
			'api_key' => '',
		],
		'expected' => 'exception',
	],
	'testShouldReturnFalseWhenEmptyEmail' => [
		'config' => [
			'email'   => '',
			'api_key' => '12345',
		],
		'expected' => 'exception',
	],
	'testShouldReturnFalseWhenEmptyAPIKey' => [
		'config' => [
			'email'   => 'roger@wp-rocket.me',
			'api_key' => '',
		],
		'expected' => 'exception',
	],
	'testShouldReturnFalseWhenInvalidEmail' => [
		'config' => [
			'email'   => 'randomstring',
			'api_key' => '12345',
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenCredentialsValid' => [
		'config' => [
			'email'   => 'roger@wp-rocket.me',
			'api_key' => '12345',
		],
		'expected' => true,
	],
];
