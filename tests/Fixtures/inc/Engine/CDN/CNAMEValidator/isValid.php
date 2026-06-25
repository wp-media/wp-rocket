<?php

return [
	'returnsTrueWhenTransientCachedAsValid'   => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => 1,
			'is_wp_error'  => false,
			'response'     => [],
			'response_code' => 0,
		],
		'expected' => [
			'result'          => true,
			'transient_value' => null,
		],
	],
	'returnsFalseWhenTransientCachedAsInvalid' => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => 0,
			'is_wp_error'  => false,
			'response'     => [],
			'response_code' => 0,
		],
		'expected' => [
			'result'          => false,
			'transient_value' => null,
		],
	],
	'returnsTrueAndCachesWhenWpError'         => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => false,
			'is_wp_error'  => true,
			'response'     => [ 'wp_error' => true ],
			'response_code' => 0,
		],
		'expected' => [
			'result'          => true,
			'transient_value' => 1,
		],
	],
	'returnsFalseAndCachesWhenResponseIs404'  => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => false,
			'is_wp_error'  => false,
			'response'     => [ 'response' => [ 'code' => 404 ] ],
			'response_code' => 404,
		],
		'expected' => [
			'result'          => false,
			'transient_value' => 0,
		],
	],
	'returnsTrueAndCachesWhenResponseIs200'   => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => false,
			'is_wp_error'  => false,
			'response'     => [ 'response' => [ 'code' => 200 ] ],
			'response_code' => 200,
		],
		'expected' => [
			'result'          => true,
			'transient_value' => 1,
		],
	],
	'returnsTrueAndCachesWhenResponseIs301'   => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => false,
			'is_wp_error'  => false,
			'response'     => [ 'response' => [ 'code' => 301 ] ],
			'response_code' => 301,
		],
		'expected' => [
			'result'          => true,
			'transient_value' => 1,
		],
	],
	'returnsTrueAndCachesWhenResponseIs403'   => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => false,
			'is_wp_error'  => false,
			'response'     => [ 'response' => [ 'code' => 403 ] ],
			'response_code' => 403,
		],
		'expected' => [
			'result'          => true,
			'transient_value' => 1,
		],
	],
	'returnsTrueAndCachesWhenResponseIs500'   => [
		'config'   => [
			'cname_url'    => 'https://cdn.example.com',
			'cached_value' => false,
			'is_wp_error'  => false,
			'response'     => [ 'response' => [ 'code' => 500 ] ],
			'response_code' => 500,
		],
		'expected' => [
			'result'          => true,
			'transient_value' => 1,
		],
	],
];
