<?php

$expected = [
	'rocket_clean_domain_urls' => [ 'http://example.org' ],
	'cleaned'                  => [
		'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
		'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
		'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
	],
];

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

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
