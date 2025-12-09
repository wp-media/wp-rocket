<?php

return [
	'testWhenVarnishNotRunningNoHeaders' => [
		'headers'       => [],
		'config_server' => [],
		'expected'      => [],
	],
	'testWhenVarnishNotRunningWithHeaders' => [
		'headers'       => [
			'X-Purge-Method' => 'regex',
		],
		'config_server' => [],
		'expected'      => [
			'X-Purge-Method' => 'regex',
		],
	],
	'testWhenVarnishNotRunningMissingApp' => [
		'headers'       => [],
		'config_server' => [
			'HTTP_X_VARNISH' => 'HTTP_X_VARNISH',
		],
		'expected'      => [],
	],
	'testWhenVarnishPassMode' => [
		'headers'       => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'varnishpass',
		],
		'expected'      => [],
	],
	'testWhenVarnishRunningEmptyHeaders' => [
		'headers'       => [],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'HTTP_X_APPLICATION',
		],
		'expected'      => [
			'X-Real-IP' => '127.0.0.1',
		],
	],
	'testWhenVarnishRunningWithExistingHeaders' => [
		'headers'       => [
			'X-Purge-Method' => 'regex',
			'User-Agent'     => 'WP Rocket',
		],
		'config_server' => [
			'HTTP_X_VARNISH'     => 'HTTP_X_VARNISH',
			'HTTP_X_APPLICATION' => 'wordpress',
		],
		'expected'      => [
			'X-Purge-Method' => 'regex',
			'User-Agent'     => 'WP Rocket',
			'X-Real-IP'      => '127.0.0.1',
		],
	],
];
