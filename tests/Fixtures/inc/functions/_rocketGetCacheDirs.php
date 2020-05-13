<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                => [
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-123456' => [
						'index.html_gzip' => '',
					],
					'example.org-tester-987654'  => [
						'index.html_gzip' => '',
					],

					'baz.example.org'             => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz1-123456' => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz2-987654' => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz3-456789' => [
						'index.html_gzip' => '',
					],

					'wp.baz.example.org'               => [
						'index.html_gzip' => '',
					],
					'wp.baz.example.org-wpbaz1-123456' => [
						'index.html_gzip' => '',
					],

					'example.org#fr' => [
						'index.html_gzip' => '',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'non_cached' => [
			'shouldReturnDomainAndUserCaches'                => [
				'config'   => [
					'url_host' => 'example.org',
				],
				'expected' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
					'vfs://public/wp-content/cache/wp-rocket/example.org#fr',
				],
			],
			'shouldReturnDomainAndUserCaches_cachePathGiven' => [
				'config'   => [
					'url_host'   => 'example.org',
					'cache_path' => 'vfs://public/wp-content/cache/wp-rocket/',
				],
				'expected' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
					'vfs://public/wp-content/cache/wp-rocket/example.org#fr',
				],
			],

			'shouldReturnSubDomainAndUserCaches'             => [
				'config'   => [
					'url_host' => 'baz.example.org',
				],
				'expected' => [
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org',
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org-baz1-123456',
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org-baz2-987654',
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org-baz3-456789',
				],
			],
			'shouldReturnDomainAndUserCaches_cachePathGiven' => [
				'config'   => [
					'url_host'   => 'baz.example.org',
					'cache_path' => 'vfs://public/wp-content/cache/wp-rocket/',
				],
				'expected' => [
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org',
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org-baz1-123456',
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org-baz2-987654',
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org-baz3-456789',
				],
			],

			'shouldReturnSubDomainAndUserCaches'                   => [
				'config'   => [
					'url_host' => 'wp.baz.example.org',
				],
				'expected' => [
					'vfs://public/wp-content/cache/wp-rocket/wp.baz.example.org',
					'vfs://public/wp-content/cache/wp-rocket/wp.baz.example.org-wpbaz1-123456',
				],
			],
			'shouldReturnSubSubDomainAndUserCaches_cachePathGiven' => [
				'config'   => [
					'url_host'   => 'wp.baz.example.org',
					'cache_path' => 'vfs://public/wp-content/cache/wp-rocket/',
				],
				'expected' => [
					'vfs://public/wp-content/cache/wp-rocket/wp.baz.example.org',
					'vfs://public/wp-content/cache/wp-rocket/wp.baz.example.org-wpbaz1-123456',
				],
			],
		],

		'crawlOnce' => [
			[ 'example.org' ],
			[ 'baz.example.org' ],
			[ 'wp.baz.example.org' ],
		],
	],
];
