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
		'shouldDeleteSingleDirUrl'                              => [
			'urls'     => [
				'http://baz.example.org/',
			],
			'configs' => [
				'post_id' => 0,
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
			'configs' => [
				'post_id' => 0,
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
		'shouldDeleteSiteUrlInCacheAndUserCaches'               => [
			'urls'     => [
				'http://example.org/',
			],
			'configs' => [
				'post_id' => 0,
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
			],
		],
		'shouldOnlyDeleteQueryStringDirForHomeUrlWithQuery'     => [
			'urls'     => [
				'http://example.org/?p=245',
			],
			'configs' => [
				'post_id' => 0,
				'create'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/#p=245/index.html',
				],
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/#p=245' => [],
				],
			],
		],
		'shouldOnlyDeleteQueryStringDirForLanguageUrlWithQuery' => [
			'urls'     => [
				'http://example.org/de/?p=245/',
			],
			'configs' => [
				'post_id' => 0,
				'create'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/#p=245%2f/index.html',
				],
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/#p=245%2f' => [],
				],
			],
		],
		'shouldDeletePageDirWhenOnlyIgnoredParameters'     => [
			'urls'     => [
				'http://example.org/lorem-ipsum/?utm_source=test',
			],
			'configs'  => [
				'post_id'        => 0,
				'ignored_params' => [ 'utm_source' => 1 ],
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/' => null,
				],
			],
		],
		'shouldDeleteOnlyHomeFilesWhenOnlyIgnoredParameters' => [
			'urls'     => [
				'http://example.org/?utm_source=test',
			],
			'configs'  => [
				'post_id'        => 0,
				'ignored_params' => [ 'utm_source' => 1 ],
				'i18n_uri'       => [ 'http://example.org/' ],
			],
			'expected' => [
				'kept'    => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/index.html',
				],
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip' => null,
				],
			],
		],
		'shouldDeleteOnlyHomeFilesWhenSeveralIgnoredParameters' => [
			'urls'     => [
				'http://example.org/?utm_source=test&fbclid=abc&gclid=xyz',
			],
			'configs'  => [
				'post_id'        => 0,
				'ignored_params' => [
					'utm_source' => 1,
					'fbclid'     => 1,
					'gclid'      => 1,
				],
				'i18n_uri'       => [ 'http://example.org/' ],
			],
			'expected' => [
				'kept'    => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/index.html',
				],
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip' => null,
				],
			],
		],
		'shouldDeleteOnlyLanguageHomeFilesWhenOnlyIgnoredParameters' => [
			'urls'     => [
				'http://example.org/fr/?fbclid=abc',
			],
			'configs'  => [
				'post_id'        => 0,
				'ignored_params' => [ 'fbclid' => 1 ],
				'i18n_uri'       => [ 'http://example.org/', 'http://example.org/fr/' ],
			],
			'expected' => [
				'kept'    => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/lorem-ipsum/index.html',
				],
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index-mobile.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index-mobile.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/index.html_gzip' => null,
				],
			],
		],
		'shouldDeleteOnlyLanguageHomeFilesWhenEncodedPathAndIgnoredParameters' => [
			'urls'     => [
				'http://example.org/%66r/?fbclid=abc',
			],
			'configs'  => [
				'post_id'        => 0,
				'ignored_params' => [ 'fbclid' => 1 ],
				'i18n_uri'       => [ 'http://example.org/', 'http://example.org/fr/' ],
			],
			'expected' => [
				'kept'    => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/lorem-ipsum/index.html',
				],
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index-mobile.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index-mobile.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/index.html' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/index.html_gzip' => null,
				],
			],
		],
		'shouldDeleteSortedQueryStringDirWithoutIgnored'   => [
			'urls'     => [
				'http://example.org/?utm_source=test&size=xl&color=red',
			],
			'configs'  => [
				'post_id'        => 0,
				'ignored_params' => [ 'utm_source' => 1 ],
				'create'         => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/#color=red&size=xl/index.html',
				],
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/#color=red&size=xl' => [],
				],
			],
		],
		'shouldDeleteQueryStringDirWithEncodedValue'       => [
			'urls'     => [
				'http://example.org/?color=caf%C3%A9%20au%20lait',
			],
			'configs'  => [
				'post_id' => 0,
				'create'  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/#color=caf%c3%a9+au+lait/index.html',
				],
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/#color=caf%c3%a9+au+lait' => [],
				],
			],
		],
	],
];
