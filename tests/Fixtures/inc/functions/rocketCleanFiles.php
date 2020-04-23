<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldBailOutWhenNoURLsToClean'              => [
			'urls'     => [],
			'expected' => [
				'cleaned'     => [],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,

					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,

					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/' => true,
				],
			],
		],
		'shouldDeleteSingleUrl'                       => [
			'urls'     => [
				'http://baz.example.org/',
			],
			'expected' => [
				'dump_results' => true,
				'cleaned'      => [
					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => [],
				],
				'non_cleaned'  => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,

					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,

					'vfs://public/wp-content/cache/wp-rocket/example.org/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/' => true,
				],
			],
		],
		'shouldDeletePageUrlInCacheAndUserCache'      => [
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
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,

					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,

					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org/'                       => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'             => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'        => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip' => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/'                  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'          => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'       => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/'             => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/' => true,
				],
			],
		],
		'shouldDeleteChildPageUrlInCacheAndUserCache' => [
			'urls'     => [
				'http://example.org/nec-ullamcorper/enim-nunc-faucibus/',
			],
			'expected' => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/enim-nunc-faucibus/' => null,
				],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,

					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,

					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org/'                                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html'               => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip'          => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/'                           => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/'                       => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                   => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html_gzip' => false,

					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'                                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'                      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de/'                             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'                             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/index.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/index.html_gzip' => false,
				],
			],
		],
		'shouldDeleteLangUrlInCacheAndUserCaches'     => [
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
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,

					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,

					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => true,

					'vfs://public/wp-content/cache/wp-rocket/example.org/'                       => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'             => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'        => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip' => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/'                  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'          => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'           => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'       => true,

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
				],
			],
		],
		'shouldDeleteSiteUrlInCacheAndUserCaches'     => [
			'urls'     => [
				'http://example.org/',
			],
			'expected' => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,

					'vfs://public/wp-content/cache/wp-rocket/' => false,

					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,

					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => true,
				],
			],
		],
	],
];
