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

//		'shouldDeleteAll_*example.org*_whenLangGiven' => [
//			'i18n'      => [
//				'lang'             => 'fr',
//				'data'             => [],
//				'i18n_plugin'      => false,
//				'dirs_to_preserve' => [],
//			],
//			'expected'  => [
//				'rocket_clean_domain_urls' => [ 'http://example.org' ],
//				'cleaned'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
//				],
//				'non_cleaned'              => [
//					// fs entry => should scan the directory and get the file listings.
//					'vfs://public/wp-content/cache/min/'                 => true,
//					'vfs://public/wp-content/cache/busting/'             => true,
//					'vfs://public/wp-content/cache/critical-css/'        => true,
//					'vfs://public/wp-content/cache/wp-rocket/'           => false,
//					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,
//				],
//			],
//			'unit_test' => [
//				'rocket_has_i18n'          => false,
//				'get_rocket_i18n_uri'      => null,
//				'get_rocket_i18n_home_url' => 'http://example.org',
//				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
//				'rocket_rrmdir'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org',
//				],
//			],
//		],
//
//		'wpml_shouldDeleteAll_*example.org*_whenLangGiven' => [
//			'i18n'      => [
//				'lang'             => 'fr',
//				'data'             => $i18n_plugins['wpml'],
//				'i18n_plugin'      => 'wpml',
//				'dirs_to_preserve' => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
//				],
//			],
//			'expected'  => [
//				'rocket_clean_domain_urls' => [ 'http://example.org?lang=fr' ],
//				'cleaned'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
//				],
//				'non_cleaned'              => [
//					// fs entry => should scan the directory and get the file listings.
//					'vfs://public/wp-content/cache/min/'                 => true,
//					'vfs://public/wp-content/cache/busting/'             => true,
//					'vfs://public/wp-content/cache/critical-css/'        => true,
//					'vfs://public/wp-content/cache/wp-rocket/'           => false,
//					'vfs://public/wp-content/cache/wp-rocket/index.html' => false,
//				],
//			],
//			'unit_test' => [
//				'rocket_has_i18n'          => false,
//				'get_rocket_i18n_uri'      => null,
//				'get_rocket_i18n_home_url' => 'http://example.org?lang=fr',
//				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
//				'rocket_rrmdir'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org',
//				],
//			],
//		],
//
//		'qtranslate_shouldDeleteDirs_en' => [
//			'i18n'      => [
//				'lang'             => 'en',
//				'data'             => $i18n_plugins['qtranslate'],
//				'i18n_plugin'      => 'qtranslate',
//				'dirs_to_preserve' => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
//				],
//			],
//			'expected'  => [
//				'rocket_clean_domain_urls' => [ 'http://example.org' ],
//				'cleaned'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                  => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                   => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'               => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/' => null,
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'                          => [],
//				],
//				'non_cleaned'              => [
//					// fs entry => should scan the directory and get the file listings.
//					'vfs://public/wp-content/cache/min/'                                     => true,
//					'vfs://public/wp-content/cache/busting/'                                 => true,
//					'vfs://public/wp-content/cache/critical-css/'                            => true,
//					'vfs://public/wp-content/cache/wp-rocket/'                               => false,
//					'vfs://public/wp-content/cache/wp-rocket/index.html'                     => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/'                   => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'    => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/' => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/' => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'     => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'  => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de/'  => true,
//				],
//			],
//			'unit_test' => [
//				'rocket_has_i18n'          => 'qtranslate',
//				'get_rocket_i18n_uri'      => null,
//				'get_rocket_i18n_home_url' => 'http://example.org',
//				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
//				'rocket_rrmdir'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org',
//				],
//			],
//		],
//
//		'qtranslate_shouldDeleteDirs_fr' => [
//			'i18n'      => [
//				'lang'             => 'fr',
//				'data'             => $i18n_plugins['qtranslate'],
//				'i18n_plugin'      => 'qtranslate',
//				'dirs_to_preserve' => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
//				],
//			],
//			'expected'  => [
//				'rocket_clean_domain_urls' => [ 'http://example.org/fr' ],
//				'cleaned'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org/fr'               => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr' => null,
//				],
//				'non_cleaned'              => [
//					// fs entry => should scan the directory and get the file listings.
//					'vfs://public/wp-content/cache/min/'                                                 => true,
//					'vfs://public/wp-content/cache/busting/'                                             => true,
//					'vfs://public/wp-content/cache/critical-css/'                                        => true,
//					'vfs://public/wp-content/cache/wp-rocket/'                                           => false,
//					'vfs://public/wp-content/cache/wp-rocket/index.html'                                 => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/'                               => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                            => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                  => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                   => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'               => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'                => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/'             => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'                 => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de/'              => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/' => true,
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'                          => true,
//				],
//			],
//			'unit_test' => [
//				'rocket_has_i18n'          => 'qtranslate',
//				'get_rocket_i18n_uri'      => null,
//				'get_rocket_i18n_home_url' => 'http://example.org/fr',
//				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
//				'rocket_rrmdir'            => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr',
//				],
//			],
//		],
//
//		'polylang_shouldDeleteDirs_de'   => [
//			'i18n'      => [
//				'lang'             => 'de',
//				'data'             => $i18n_plugins['polylang'],
//				'i18n_plugin'      => 'polylang',
//				'dirs_to_preserve' => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
//					'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
//				],
//			],
//			'expected'  => [
//				'rocket_clean_domain_urls' => [ 'http://example.org/de' ],
//				'cleaned'                  => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org/de'                => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de' => null,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de'  => null,
//				],
//				'non_cleaned'              => [
//					// fs entry => should scan the directory and get the file listings.
//					'vfs://public/wp-content/cache/min/'                                                 => true,
//					'vfs://public/wp-content/cache/busting/'                                             => true,
//					'vfs://public/wp-content/cache/critical-css/'                                        => true,
//					'vfs://public/wp-content/cache/wp-rocket/'                                           => false,
//					'vfs://public/wp-content/cache/wp-rocket/index.html'                                 => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/'                               => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                            => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                  => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                   => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'               => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'                => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/'             => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'                 => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => false,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'              => true,
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/' => true,
//					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'                          => true,
//				],
//			],
//			'unit_test' => [
//				'rocket_has_i18n'          => 'qtranslate',
//				'get_rocket_i18n_uri'      => null,
//				'get_rocket_i18n_home_url' => 'http://example.org/de',
//				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org/de',
//				'rocket_rrmdir'            => [
//					'vfs://public/wp-content/cache/wp-rocket/example.org/de',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de',
//					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de',
//				],
//			],
//		],
	],
];
