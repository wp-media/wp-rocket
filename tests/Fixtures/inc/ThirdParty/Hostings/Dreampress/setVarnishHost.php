<?php

return [
	'testShouldReturnArrayWhenHostsIsString' => [
		'hosts' => '',
		'expected' => [
			'',
			'localhost',
		], 
	],
	'testShouldReturnArrayWhenHostsIsArray' => [
		'hosts' => [],
		'expected' => [
			'localhost',
		], 
	],
	'testShouldAddValueToArrayWhenHostsHasValue' => [
		'hosts' => [
			'example.org',
		],
		'expected' => [
			'example.org',
			'localhost',
		], 
	],
	'testShouldNotAddLocalhostTwiceIfAlreadyExists' => [
		'hosts' => [
			'localhost',
		],
		'expected' => [
			'localhost',
		], 
	],
];
