<?php

$expected = [
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
];

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php',

	// Test data.
	'test_data' => [

		'shouldCleanDomainWhenWidgetUpdateWithTitleOnly' => [
			'widget'   => [
				'title' => 'Duis aute irure',
				'text'  => '',
			],
			'expected' => $expected,
		],

		'shouldCleanDomainWhenWidgetUpdateWithTextOnly' => [
			'widget'   => [
				'title' => '',
				'text'  => 'Ut enim ad minim veniam',
			],
			'expected' => $expected,
		],

		'shouldCleanDomainWhenWidgetUpdateWithTitleAndText' => [
			'widget'   => [
				'title' => 'Lorem ipsum',
				'text'  => 'Ut enim ad minim veniam',
			],
			'expected' => $expected,
		],
	],
];
