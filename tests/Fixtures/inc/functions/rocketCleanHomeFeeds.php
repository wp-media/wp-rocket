<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldDeleteFeeds'    => [
			'config'   => [
				'cache_feed' => true,
				'urls'    => [
					'http://example.org/feed',
					'http://example.org/comments/feed',
				],
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/feed'          => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/comments/feed' => null,
				],
			],
		],
		'shouldNotDeleteFeeds' => [
			'config'   => [
				'cache_feed' => false,
			],
			'expected' => [
				'cleaned'  => [],
			],
		],
	],
];
