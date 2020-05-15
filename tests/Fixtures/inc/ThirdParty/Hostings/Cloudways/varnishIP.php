<?php

return [
	'testWhenEmptyArray' => [
		'varnish_ip' => [],
		'expected'   => [
			'127.0.0.1:8080',
		],
	],
	'testWhenArrayWithData' => [
		'varnish_ip' => [
			'localhost',
		],
		'expected'   => [
			'localhost',
			'127.0.0.1:8080',
		],
	],
	'testWhenString' => [
		'varnish_ip' => 'localhost',
		'expected'   => [
			'localhost',
			'127.0.0.1:8080',
		],
	],
	'testWhenBool' => [
		'varnish_ip' => false,
		'expected'   => [
			false,
			'127.0.0.1:8080',
		],
	],
];
