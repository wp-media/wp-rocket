<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldDeleteAll_example.org*' => [
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
			],
		],
	],
];
