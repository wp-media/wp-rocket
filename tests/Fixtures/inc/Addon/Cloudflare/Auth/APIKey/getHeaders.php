<?php

return [
	'testShouldReturnCredentialsArray' => [
		'config' => [
			'email'   => 'roger@wp-rocket.me',
			'api_key' => '12345',
		],
		'expected' => [
			'X-Auth-Email' => 'roger@wp-rocket.me',
			'X-Auth-Key'   => '12345',
		],
	],
];
