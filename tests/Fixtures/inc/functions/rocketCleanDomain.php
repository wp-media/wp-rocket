<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php',

	// Test data.
	'test_data' => [

		'shouldDeleteAll_example.org*_whenNoLangGiven' => [
			'i18n'      => [
				'lang'             => '',
				'data'             => [],
				'i18n_plugin'      => false,
				'dirs_to_preserve' => [],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
				],
				'non_cleaned'              => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                 => true,
					'vfs://public/wp-content/cache/busting/'             => true,
					'vfs://public/wp-content/cache/critical-css/'        => true,
					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => false,
				'get_rocket_i18n_uri'      => [ 'http://example.org' ],
				'get_rocket_i18n_home_url' => null,
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
			],
		],

		'shouldDeleteAll_*example.org*_whenLangGiven' => [
			'i18n' => [
				'lang'             => 'fr',
				'data'             => [],
				'i18n_plugin'      => false,
				'dirs_to_preserve' => [],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
				],
				'non_cleaned'              => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                 => true,
					'vfs://public/wp-content/cache/busting/'             => true,
					'vfs://public/wp-content/cache/critical-css/'        => true,
					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => false,
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
			],
		],

		'qtranslate_shouldDeleteAllExceptLangDirs' => [
			'i18n' => [
				'lang'             => 'fr',
				'data'             => $i18n_plugins['qtranslate'],
				'i18n_plugin'      => 'qtranslate',
				'dirs_to_preserve' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/en',
					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org/fr' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr' => null,
				],
				'non_cleaned'              => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => 'qtranslate',
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org/fr',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
			],
		],
	],
];
