<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldBailOutWhenNoURLsToClean'                        => [
			'urls'     => [],
			'expected' => [
				'cleaned' => [],
			],
		],
		'shouldDeleteSingleDirUrl'                              => [
			'urls'     => [
				'http://baz.example.org/',
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => [],
				],
			],
		],
		'shouldDeleteSingleFileUrlFromDomainAndUserCaches'      => [
			'urls'     => [
				'http://example.org/index.html',
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'  => null,
				],
			],
		],
		'shouldDeleteGrandchildFilesUrlFromDomainAndUserCaches' => [
			'urls'     => [
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/index.html',
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
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
		'shouldDeleteSiteUrlInCacheAndUserCaches'               => [
			'urls'     => [
				'http://example.org/',
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
			],
		],
	],
];
