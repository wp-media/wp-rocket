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
				'error'    => [
					'http://example.org/not-cached-page/' => 'No cache found for this URL, or a file permission issue prevented cache clearing.',
				],
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
				'error'    => [
					'http://example.org/not-cached-page/' => 'No cache found for this URL, or a file permission issue prevented cache clearing.',
				],
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
					'http://example.org/not-cached-page-1/' => 'No cache found for this URL, or a file permission issue prevented cache clearing.',
					'http://example.org/not-cached-page-2/' => 'No cache found for this URL, or a file permission issue prevented cache clearing.',
				],
			],
		],

		'testShouldReturnErrorForInvalidUrl'                => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => 'not-a-valid-url',
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'not-a-valid-url' => 'URL format is invalid.',
				],
			],
		],

		'testShouldReturnMixedErrorsForInvalidAndUncachedUrls' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/lorem-ipsum/',
						'http://example.org/not-cached-page/',
						'not-a-valid-url',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'not-a-valid-url'                     => 'URL format is invalid.',
					'http://example.org/not-cached-page/' => 'No cache found for this URL, or a file permission issue prevented cache clearing.',
				],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],

		'testShouldReturnErrorForExternalDomainUrl'         => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => 'https://external-domain.com/some-page/',
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'https://external-domain.com/some-page/' => 'URL does not belong to this site.',
				],
			],
		],

		'testShouldReturnMixedErrorsForExternalAndCachedUrls' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/lorem-ipsum/',
						'https://external-domain.com/some-page/',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'https://external-domain.com/some-page/' => 'URL does not belong to this site.',
				],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],

		'testShouldReturnErrorForAdminUrl'                  => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => 'http://example.org/wp-admin/post.php?post=919&action=edit',
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'http://example.org/wp-admin/post.php?post=919&action=edit' => 'URL is an admin page.',
				],
			],
		],

		'testShouldReturnMixedErrorsForAdminAndCachedUrls'  => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/lorem-ipsum/',
						'http://example.org/wp-admin/post.php?post=919&action=edit',
					],
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => [
					'http://example.org/wp-admin/post.php?post=919&action=edit' => 'URL is an admin page.',
				],
				'cleaned'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
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

		'testShouldRouteHomeUrlToCleanHomeOnly'              => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => 'http://example.org/',
				],
			],
			'expected' => [
				'is_error'           => false,
				'success'            => true,
				'error'              => [],
				// Home url must go through rocket_clean_home(), never through rocket_clean_files().
				'clean_home_calls'   => 1,
				'clean_files_calls'  => 0,
			],
		],

		'testShouldRouteHomeUrlSeparatelyFromOtherRequestedUrl' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'url' => [
						'http://example.org/',
						'http://example.org/lorem-ipsum/',
					],
				],
			],
			'expected' => [
				'is_error'          => false,
				'success'           => true,
				'error'             => [],
				// Home url is routed to rocket_clean_home(), the other url is still cleared via rocket_clean_files().
				'clean_home_calls'  => 1,
				'clean_files_calls' => 1,
				'cleaned'           => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],
	],
];
