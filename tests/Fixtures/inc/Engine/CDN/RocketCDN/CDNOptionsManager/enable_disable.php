<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldDeleteAll' => [
			'cleaned'      => [
				'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
				'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
				'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
			],
		],
	],
];
