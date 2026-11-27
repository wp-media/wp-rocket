<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// example.org matches WP_TESTS_DOMAIN, so rocket_clean_domain() targets this dir.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html' => '',
						'page'       => [
							'index.html' => '',
						],
					],
				],
			],
		],
	],

	'test_data' => [
		'testShouldReturnErrorWhenUserLacksPermission' => [
			'config'   => [
				'has_permission' => false,
				'is_importing'   => false,
				'has_urls'       => true,
			],
			'expected' => [
				'is_error' => true,
				'cleaned'  => [],
			],
		],

		'testShouldClearCacheWhenUserHasPermission'    => [
			'config'   => [
				'has_permission' => true,
				'is_importing'   => false,
				'has_urls'       => true,
			],
			'expected' => [
				'is_error' => false,
				'success'  => true,
				'error'    => '',
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/' => null,
				],
			],
		],

		'testShouldFailWhenImportIsInProgress'         => [
			'config'   => [
				'has_permission' => true,
				'is_importing'   => true,
				'has_urls'       => true,
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => 'Unable to clear cache: a content import is currently in progress.',
				'cleaned'  => [],
			],
		],

		'testShouldFailWhenNoCacheableUrlsExist'       => [
			'config'   => [
				'has_permission' => true,
				'is_importing'   => false,
				'has_urls'       => false,
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => 'Unable to clear cache: no cacheable URLs were found for this site.',
				'cleaned'  => [],
			],
		],
	],
];
