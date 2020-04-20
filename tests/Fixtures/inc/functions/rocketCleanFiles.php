<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php',

	// Test data.
	'test_data' => [

		'shouldDeleteSingleUrl' => [
			'urls'     => [
				'http://dots.example.org/',
			],
			'expected' => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/' => [],
				],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
				],
			],
		],

		'shouldDeletePageUrlInCacheAndUserCache' => [
			'urls'     => [
				'http://example.org/lorem-ipsum/',
			],
			'expected' => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                                 => true,
					'vfs://public/wp-content/cache/busting/'                                             => true,
					'vfs://public/wp-content/cache/critical-css/'                                        => true,
					'vfs://public/wp-content/cache/wp-rocket/'                                           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                               => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                            => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                            => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'               => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'                 => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'                          => true,
				],
			],
		],
		'shouldDeleteLangUrlInCacheAndUserCaches' => [
			'urls'     => [
				'http://example.org/fr/',
			],
			'expected' => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'  => null,
				],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                                 => true,
					'vfs://public/wp-content/cache/busting/'                                             => true,
					'vfs://public/wp-content/cache/critical-css/'                                        => true,
					'vfs://public/wp-content/cache/wp-rocket/'                                           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                               => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                            => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                   => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'               => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/' => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'                          => true,
				],
			],
		],
	],
];
