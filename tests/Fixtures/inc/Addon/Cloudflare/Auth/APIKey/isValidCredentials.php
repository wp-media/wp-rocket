<?php

return [
	'testShouldReturnWPErrorWhenEmptyCredentials' => [
		'config' => [
			'email'   => '',
			'api_key' => '',
		],
		'expected' => 'error',
	],
	'testShouldReturnWPErrorWhenEmptyEmail' => [
		'config' => [
			'email'   => '',
			'api_key' => '12345',
		],
		'expected' => 'error',
	],
	'testShouldReturnWPErrorWhenEmptyAPIKey' => [
		'config' => [
			'email'   => 'roger@wp-rocket.me',
			'api_key' => '',
		],
		'expected' => 'error',
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
