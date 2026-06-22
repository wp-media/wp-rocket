<?php

return [
	'settings'  => [
		'cache_mobile'            => 1,
		'do_caching_mobile_files' => 0,
		'cache_webp'              => 1,
		'cache_logged_user'       => 0,
		'minify_css'              => 1,
		'lazyload'                => 1,
		'cdn'                     => 0,
		'preload_fonts'           => [ 'https://example.com/font.woff2' ],
		'dns_prefetch'            => [ 'example.com' ],
		// Denylist keys - should be filtered out.
		'secret_cache_key'        => 'secret-value',
		'consumer_key'            => 'consumer-secret',
		'license'                 => 'license-secret',
	],
	'test_data' => [
		'testShouldReturnOptionsWhenUserHasPermission' => [
			'config'   => [
				'has_permission' => true,
			],
			'expected' => [
				'is_error' => false,
				'data'     => [
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 0,
					'cache_webp'              => 1,
					'cache_logged_user'       => 0,
					'minify_css'              => 1,
					'lazyload'                => 1,
					'cdn'                     => 0,
					'preload_fonts'           => [ 'https://example.com/font.woff2' ],
					'dns_prefetch'            => [ 'example.com' ],
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
