<?php

return [
	'testWhenEmptyArray' => [
		'varnish_ip'    => [],
		'expected'      => [
			'127.0.0.1:6081',
		],
	],
	'testWhenArrayWithData' => [
		'varnish_ip'    => [
			'localhost',
		],
		'expected'      => [
			'localhost',
			'127.0.0.1:6081',
		],
	],
	'testWhenString' => [
		'varnish_ip'    => 'localhost',
		'expected'      => [
			'localhost',
			'127.0.0.1:6081',
		],
	],
];
