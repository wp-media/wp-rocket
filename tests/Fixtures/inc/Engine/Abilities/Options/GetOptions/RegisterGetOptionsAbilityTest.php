<?php

return [
	'settings'  => [
		'cache_webp'        => 1,
		'cache_logged_user' => 0,
		'minify_css'        => 1,
		'lazyload'          => 1,
		'cdn'               => 0,
		'preload_fonts'     => [ 'https://example.com/font.woff2' ],
		'dns_prefetch'      => [ 'example.com' ],
		// Keys outside the allowlist — must not appear in results.
		'secret_cache_key'  => 'secret-value',
		'consumer_key'      => 'consumer-secret',
		'license'           => 'license-secret',
		'analytics_enabled' => 1,
	],
	'test_data' => [
		'testShouldReturnOptionsWhenUserHasPermission' => [
			'config'   => [
				'has_permission' => true,
			],
			'expected' => [
				'is_error' => false,
				'data'     => [
					'cache_webp'        => 1,
					'cache_logged_user' => 0,
					'minify_css'        => 1,
					'lazyload'          => 1,
					'cdn'               => 0,
					'preload_fonts'     => [ 'https://example.com/font.woff2' ],
					'dns_prefetch'      => [ 'example.com' ],
				],
			],
		],

		'testShouldReturnErrorWhenUserLacksPermission' => [
			'config'   => [
				'has_permission' => false,
			],
			'expected' => [
				'is_error' => true,
			],
		],
	],
];
