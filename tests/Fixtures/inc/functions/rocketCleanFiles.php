<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldBailOutWhenNoURLsToClean'              => [
			'urls'     => [],
			'expected' => [
				'cleaned' => [],
			],
		],
		'shouldDeleteSingleUrl'                       => [
			'urls'     => [
				'http://baz.example.org/',
			],
			'expected' => [
				'cleaned'      => [
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => [],
				],
			],
		],
		'shouldDeletePageUrlInCacheAndUserCache'      => [
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
		'shouldDeleteChildPageUrlInCacheAndUserCache' => [
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
		'shouldDeleteLangUrlInCacheAndUserCaches'     => [
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
		'shouldDeleteSiteUrlInCacheAndUserCaches'     => [
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
