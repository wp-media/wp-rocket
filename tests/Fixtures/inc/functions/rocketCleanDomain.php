<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldDeleteAll_example.org*_whenNoLangGiven' => [
			'i18n'      => [
				'lang'             => '',
				'data'             => [],
				'i18n_plugin'      => false,
				'dirs_to_preserve' => [],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => false,
				'get_rocket_i18n_uri'      => [ 'http://example.org' ],
				'get_rocket_i18n_home_url' => null,
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
				'rocket_rrmdir'            => [
					'vfs://public/wp-content/cache/wp-rocket/example.org',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
				],
			],
		],

		'shouldDeleteAll_*example.org*_whenLangGiven' => [
			'i18n'      => [
				'lang'             => 'fr',
				'data'             => [],
				'i18n_plugin'      => false,
				'dirs_to_preserve' => [],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => false,
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
				'rocket_rrmdir'            => [
					'vfs://public/wp-content/cache/wp-rocket/example.org',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
				],
			],
		],

		'wpml_shouldDeleteAll_*example.org*_whenLangGiven' => [
			'i18n'      => [
				'lang'             => 'fr',
				'data'             => $i18n_plugins['wpml'],
				'i18n_plugin'      => 'wpml',
				'dirs_to_preserve' => [
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
				],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org?lang=fr' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => false,
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org?lang=fr',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
				'rocket_rrmdir'            => [
					'vfs://public/wp-content/cache/wp-rocket/example.org',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
				],
			],
		],

		'qtranslate_shouldDeleteDirs_en' => [
			'i18n'      => [
				'lang'             => 'en',
				'data'             => $i18n_plugins['qtranslate'],
				'i18n_plugin'      => 'qtranslate',
				'dirs_to_preserve' => [
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/fr',
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/de',
				],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'                  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'                   => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/' => null,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => 'qtranslate',
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org',
				'rocket_rrmdir'            => [
					'vfs://public/wp-content/cache/wp-rocket/example.org',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654',
				],
			],
		],

		'qtranslate_shouldDeleteDirs_fr' => [
			'i18n'      => [
				'lang'             => 'fr',
				'data'             => $i18n_plugins['qtranslate'],
				'i18n_plugin'      => 'qtranslate',
				'dirs_to_preserve' => [
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/de',
				],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org/fr' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'  => null,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => 'qtranslate',
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org/fr',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
				'rocket_rrmdir'            => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr',
				],
			],
		],

		'polylang_shouldDeleteDirs_de' => [
			'i18n'      => [
				'lang'             => 'de',
				'data'             => $i18n_plugins['polylang'],
				'i18n_plugin'      => 'polylang',
				'dirs_to_preserve' => [
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
					'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/fr',
				],
			],
			'expected'  => [
				'rocket_clean_domain_urls' => [ 'http://example.org/de' ],
				'cleaned'                  => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de/'  => null,
				],
			],
			'unit_test' => [
				'rocket_has_i18n'          => 'qtranslate',
				'get_rocket_i18n_uri'      => null,
				'get_rocket_i18n_home_url' => 'http://example.org/de',
				'root'                     => 'vfs://public/wp-content/cache/wp-rocket/example.org/de',
				'rocket_rrmdir'            => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/de',
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de',
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de',
				],
			],
		],
	],
];
