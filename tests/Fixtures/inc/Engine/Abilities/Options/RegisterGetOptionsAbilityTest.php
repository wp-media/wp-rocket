<?php

return [
	'test_data' => [
		'testShouldReturnOptionsWhenUserHasPermission' => [
			'config'   => [
				'has_permission' => true,
				'settings'       => [
					'cache_webp'        => 1,
					'cache_logged_user' => 0,
					'minify_css'        => 1,
					'lazyload'          => 1,
					'cdn'               => 0,
					// Denylist keys - should be filtered out.
					'secret_cache_key'  => 'secret-value',
					'consumer_key'      => 'consumer-secret',
					'license'           => 'license-secret',
				],
			],
			'expected' => [
				'is_error' => false,
				'data'     => [
					'cache_webp'        => 1,
					'cache_logged_user' => 0,
					'minify_css'        => 1,
					'lazyload'          => 1,
					'cdn'               => 0,
				],
			],
		],

		'testShouldReturnErrorWhenUserLacksPermission' => [
			'config'   => [
				'has_permission' => false,
				'settings'       => [
					'cache_webp'   => 1,
					'minify_css'   => 1,
					'consumer_key' => 'consumer-secret',
				],
			],
			'expected' => [
				'is_error' => true,
			],
		],
	],
];
