<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'testShouldReturnWPErrorWhenUserLacksPermission'   => [
			'config'   => [
				'has_permission' => false,
				'input'          => [
					'url' => 'http://example.org/lorem-ipsum/',
				],
			],
			'expected' => [
				'is_error' => true,
			],
		],

		'testShouldClearSingleCachedUrl'                   => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => 'http://example.org/lorem-ipsum/',
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => true,
				'error'    => [],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],

		'testShouldReturnErrorForUncachedUrl'               => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => 'http://example.org/not-cached-page/',
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [ 'http://example.org/not-cached-page/' ],
			],
		],

		'testShouldClearMultipleCachedUrls'                 => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/lorem-ipsum/',
						'http://example.org/fr/',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => true,
				'error'    => [],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                         => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/'          => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'           => null,
				],
			],
		],

		'testShouldReturnPartialErrorForMixOfCachedAndUncachedUrls' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/lorem-ipsum/',
						'http://example.org/not-cached-page/',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [ 'http://example.org/not-cached-page/' ],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],

		'testShouldReturnErrorForAllUncachedUrls'           => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/not-cached-page-1/',
						'http://example.org/not-cached-page-2/',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'http://example.org/not-cached-page-1/',
					'http://example.org/not-cached-page-2/',
				],
			],
		],

		'testShouldDedupeDuplicateUrlsAndClear'             => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/lorem-ipsum/',
						'http://example.org/lorem-ipsum/',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => true,
				'error'    => [],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],
	],
];
