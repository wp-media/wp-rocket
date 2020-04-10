<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache'            => [
				'wp-rocket'    => [
					'example.org'                => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'de'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'fr'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'hidden-files'    => [
							'.mobile-active' => '',
							'.no-webp'       => '',
						],
						'lorem-ipsum'     => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'nec-ullamcorper' => [
							'enim-nunc-faucibus' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
							'index.html'         => '',
							'index.html_gzip'    => '',
						],
					],
					'example.org-wpmedia-123456' => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'de'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'fr'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'lorem-ipsum'     => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
					'example.org-tester-987654'  => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'de'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'fr'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
							'lorem-ipsum'     => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
						],
						'nec-ullamcorper' => [
							'enim-nunc-faucibus' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
							'index.html'         => '',
							'index.html_gzip'    => '',
						],
					],
					'dots.example.org'           => [
						'.'               => '',
						'..'              => '',
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'index.html'                 => '',
				],
				'min'          => [
					'1' => [
						'123456.css' => '',
						'123456.js'  => '',
					],
				],
				'busting'      => [
					'1' => [
						'ga-123456.js' => '',
					],
				],
				'critical-css' => [
					'1' => [
						'front-page.php' => '',
						'blog.php'       => '',
					],
				],
			],
			'wp-rocket-config' => [
				'example.org.php' => 'test',
			],
		],
	],

	// Test data.
	'test_data' => [

		'shouldDeleteAll_example.org*_whenNoLangGiven' => [
			'i18n' => [
				'lang'                        => '',
				'data'                        => [],
				'i18n_plugin'                 => false,
				'rocket_has_i18n'             => false,
				'get_rocket_i18n_uri'         => [ 'http://example.org' ],
				'get_rocket_i18n_home_url'    => null,
				'get_rocket_i18n_to_preserve' => [],
			],

			'expected' => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
				],
				'non_cleaned'              => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                 => true,
					'vfs://public/wp-content/cache/busting/'             => true,
					'vfs://public/wp-content/cache/critical-css/'        => true,
					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,
				],
			],
		],

		'shouldDeleteAll_*example.org*_whenLangGiven' => [
			'i18n' => [
				'lang'                        => 'fr',
				'data'                        => [],
				'i18n_plugin'                 => false,
				'rocket_has_i18n'             => false,
				'get_rocket_i18n_uri'         => [ 'http://example.org' ],
				'get_rocket_i18n_home_url'    => null,
				'get_rocket_i18n_to_preserve' => [],
			],

			'expected' => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
				],
				'non_cleaned'              => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                 => true,
					'vfs://public/wp-content/cache/busting/'             => true,
					'vfs://public/wp-content/cache/critical-css/'        => true,
					'vfs://public/wp-content/cache/wp-rocket/'           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,
				],
			],
		],

		'qtranslate_shouldDeleteAllExceptLangDirs' => [
			'i18n' => [
				'lang'                        => 'fr',
				'data'                        => $i18n_plugins['qtranslate'],
				'i18n_plugin'                 => 'qtranslate',
				'rocket_has_i18n'             => 'qtranslate',
				'get_rocket_i18n_uri'         => [
					'http://example.org/en',
					'http://example.org/de',
				],
				'get_rocket_i18n_home_url'    => 'http://example.org/fr',
				'get_rocket_i18n_to_preserve' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/en',
					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				],
			],

			'expected' => [
				'rocket_clean_domain_urls' => [ 'http://example.org/fr' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr' => null,
				],
				'non_cleaned'              => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],
	],
];
