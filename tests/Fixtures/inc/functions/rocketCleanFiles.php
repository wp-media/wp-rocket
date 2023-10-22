<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldBailOutWhenNoURLsToClean'                        => [
			'urls'     => [],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [],
			],
		],
		'shouldBailOutWhenInvalidURLsToClean'                        => [
			'urls'     => [
				'test',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [],
			],
		],
		'shouldDeleteGrandchildFilesUrlFromDomainAndUserCaches' => [
			'urls'     => [
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/index.html',
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html'                    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/enim-nunc-faucibus/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip' => null,
				],
			],
		],
		'shouldDeletePageUrlInCacheAndUserCache'                => [
			'urls'     => [
				'http://example.org/lorem-ipsum/',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],
		'shouldDeleteChildPageUrlInCacheAndUserCache'           => [
			'urls'     => [
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/enim-nunc-faucibus/' => null,
				],
			],
		],
		'shouldDeleteLangUrlInCacheAndUserCaches'               => [
			'urls'     => [
				'http://example.org/fr/',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'  => null,
				],
			],
		],
		'shouldDeleteDirsAndFilesUrlInCacheAndUserCache'        => [
			'urls'     => [
				'http://example.org/category/wordpress/',
				'http://example.org/lorem-ipsum/',
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/index.html',
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/wordpress/'         => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,

					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html'                    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/enim-nunc-faucibus/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip' => null,
				],
			],
		],
	],
];
