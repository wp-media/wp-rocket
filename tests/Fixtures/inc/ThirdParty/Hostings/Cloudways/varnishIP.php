<?php

return [
	'testWhenEmptyArrayWithoutVarnishPermission' => [
		'varnish_ip'    => [],
		'config_server' => [],
		'expected'      => [],
	],
	'testWhenEmptyArrayWithoutVarnishApp' => [
		'varnish_ip'    => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
		],
		'expected'      => [],
	],
	'testWhenEmptyArrayWithoutVarnishPass' => [
		'varnish_ip'    => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'varnishpass',
		],
		'expected'      => [],
	],
	'testWhenEmptyArray' => [
		'varnish_ip'    => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			'127.0.0.1:8080',
		],
	],
	'testWhenArrayWithData' => [
		'varnish_ip'    => [
			'localhost',
		],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			'localhost',
			'127.0.0.1:8080',
		],
	],
	'testWhenString' => [
		'varnish_ip'    => 'localhost',
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			'localhost',
			'127.0.0.1:8080',
		],
	],
	'testWhenBool' => [
		'varnish_ip'    => false,
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			false,
			'127.0.0.1:8080',
		],
	],
];
