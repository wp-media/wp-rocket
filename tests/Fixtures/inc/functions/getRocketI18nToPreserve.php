<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'false_ShouldReturnEmptyArrayWhenNoLangGiven'                     => [
		'current_lang' => '',
		'config'       => [
			'data'            => [],
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
		],
		'expected'     => [],
		'mocks'        => [
			'rocket_has_i18n'          => null, // not called.
			'get_rocket_i18n_code'     => null,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],
	'false_ShouldReturnEmptyArrayWhenLangNotString'                   => [
		'current_lang' => 10, // not string.
		'config'       => [
			'data'            => [],
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
		],
		'expected'     => [],
		'mocks'        => [
			'rocket_has_i18n'          => null, // not called.
			'get_rocket_i18n_code'     => null,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],
	'false_ShouldReturnEmptyArrayWhenNoPlugin'                        => [
		'current_lang' => 'en',
		'config'       => [
			'data'            => [],
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
		],
		'expected'     => [],
		'mocks'        => [
			'get_rocket_i18n_code'     => null,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],
	'false_ShouldReturnEmptyArrayWhenNoPlugin'                        => [
		'current_lang' => 'en',
		'config'       => [
			'data'            => [
				'codes' => [ 'de', 'en', 'fr' ],
				'langs' => [ 'de', 'en', 'fr' ],
			],
			'i18n_plugin'     => 'baz',
			'rocket_has_i18n' => false,
		],
		'expected'     => [],
		'mocks'        => [
			'get_rocket_i18n_code'     => null, // not called.
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],

	/**
	 * WPML
	 */
	'wpml_ShouldReturnFrAndDe'                                        => [
		'current_lang' => 'en',
		'config'       => [
			'data'            => $i18n_plugins['wpml'],
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['wpml']['langs'],
			'get_rocket_i18n_home_url' => [
				'fr' => 'http://example.org?lang=fr',
				'de' => 'http://example.org?lang=de',
			],
			'get_rocket_parse_url'     => [
				'http://example.org?lang=fr' => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => 'lang=fr',
					'fragment' => '',
				],
				'http://example.org?lang=de' => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => 'lang=de',
					'fragment' => '',
				],
			],
		],
	],
	'wpml_ShouldReturnEnAndDe'                                        => [
		'current_lang' => 'fr',
		'config'       => [
			'data'            => $i18n_plugins['wpml'],
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
		],
		'unit_tests'   => [
			'get_rocket_i18n_code'     => $i18n_plugins['wpml']['langs'],
			'get_rocket_i18n_home_url' => [
				'en' => 'http://example.org?lang=en',
				'de' => 'http://example.org?lang=de',
			],
			'get_rocket_parse_url'     => [
				'http://example.org?lang=en' => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => 'lang=en',
					'fragment' => '',
				],
				'http://example.org?lang=de' => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => 'lang=de',
					'fragment' => '',
				],
			],
		],
	],
	'wpml_ShouldReturnEnAndFr'                                        => [
		'current_lang' => 'de',
		'config'       => [
			'data'            => $i18n_plugins['wpml'],
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
		],
		'unit_tests'   => [
			'get_rocket_i18n_code'     => $i18n_plugins['wpml']['langs'],
			'get_rocket_i18n_home_url' => [
				'en' => 'http://example.org?lang=en',
				'fr' => 'http://example.org?lang=fr',
			],
			'get_rocket_parse_url'     => [
				'http://example.org?lang=en' => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => 'lang=en',
					'fragment' => '',
				],
				'http://example.org?lang=fr' => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => 'lang=fr',
					'fragment' => '',
				],
			],
		],
	],

	/**
	 * qTranslate
	 */
	'qtranslate_ShouldReturnEmptyArray'                               => [
		'current_lang' => '',
		'config'       => [
			'data'            => $i18n_plugins['qtranslate'],
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
		],
		'expected'     => [],
		'mocks'        => [
			'rocket_has_i18n'          => null, // not called.
			'get_rocket_i18n_code'     => null,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],
	'qtranslate_ShouldReturnEmptyArrayWhenNoCodes'                    => [
		'current_lang' => 'de',
		'config'       => [
			'data'            => [ 'codes' => [] ],
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
		],
		'expected'     => [],
		'mocks'        => [
			'get_rocket_i18n_code'     => false,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],
	'qtranslate_ShouldReturnEnAndDe'                                  => [
		'current_lang' => 'fr',
		'config'       => [
			'data'                 => $i18n_plugins['qtranslate'],
			'i18n_plugin'          => 'qtranslate',
			'rocket_has_i18n'      => 'qtranslate',
			'get_rocket_i18n_code' => $i18n_plugins['polylang']['codes'],
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/de',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['wpml']['langs'],
			'get_rocket_i18n_home_url' => [
				'en' => 'http://example.org',
				'de' => 'http://example.org/de',
			],
			'get_rocket_parse_url'     => [
				'http://example.org'    => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
				'http://example.org/de' => [
					'host'     => 'example.org',
					'path'     => '/de',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
			],
		],
	],
	'qtranslate_ShouldReturnFrAndDe'                                  => [
		'current_lang' => 'en',
		'config'       => [
			'data'                 => $i18n_plugins['qtranslate'],
			'i18n_plugin'          => 'qtranslate',
			'rocket_has_i18n'      => 'qtranslate',
			'get_rocket_i18n_code' => $i18n_plugins['polylang']['codes'],
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/fr',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/de',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['wpml']['langs'],
			'get_rocket_i18n_home_url' => [
				'fr' => 'http://example.org/fr',
				'de' => 'http://example.org/de',
			],
			'get_rocket_parse_url'     => [
				'http://example.org/fr' => [
					'host'     => 'example.org',
					'path'     => '/fr',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
				'http://example.org/de' => [
					'host'     => 'example.org',
					'path'     => '/de',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
			],
		],
	],
	'qtranslate_ShouldReturnEnAndFr'                                  => [
		'current_lang' => 'de',
		'config'       => [
			'data'                 => $i18n_plugins['qtranslate'],
			'i18n_plugin'          => 'qtranslate',
			'rocket_has_i18n'      => 'qtranslate',
			'get_rocket_i18n_code' => $i18n_plugins['polylang']['codes'],
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/fr',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['wpml']['langs'],
			'get_rocket_i18n_home_url' => [
				'en' => 'http://example.org',
				'fr' => 'http://example.org/fr',
			],
			'get_rocket_parse_url'     => [
				'http://example.org'    => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
				'http://example.org/fr' => [
					'host'     => 'example.org',
					'path'     => '/fr',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
			],
		],
	],

	/**
	 * polylang
	 */
	'polylang_ShouldReturnEmptyArrayWhenNoCodes'                      => [
		'current_lang' => '',
		'config'       => [
			'data'            => [
				'codes'   => [],
				'langs'   => [],
				'options' => [],
			],
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => false,
		],
		'expected'     => [],
		'mocks'        => [
			'rocket_has_i18n'          => null, // not called.
			'get_rocket_i18n_code'     => null,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],
	],
	'polylang_ShouldReturnEmptyArrayWhenNoCodesAndPolylangConfigured' => [
		'current_lang' => 'en',
		'config'       => [
			'data'            => [
				'codes'   => [],
				'langs'   => [],
				'options' => [],
			],
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
		],
		'expected'     => [],
		'mocks'        => [
			'get_rocket_i18n_code'     => false,
			'get_rocket_i18n_home_url' => null,
			'get_rocket_parse_url'     => null,
		],

	],
	'polylang_shouldReturnLangsWhenEn'                                => [
		'current_lang' => 'en',
		'config'       => [
			'data'                 => $i18n_plugins['polylang'],
			'i18n_plugin'          => 'polylang',
			'rocket_has_i18n'      => 'polylang',
			'get_rocket_i18n_code' => $i18n_plugins['polylang']['codes'],
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/fr',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/de',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['polylang']['codes'],
			'get_rocket_i18n_home_url' => [
				'fr' => 'http://example.org/fr',
				'de' => 'http://example.org/de',
			],
			'get_rocket_parse_url'     => [
				'http://example.org/fr' => [
					'host'     => 'example.org',
					'path'     => '/fr',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
				'http://example.org/de' => [
					'host'     => 'example.org',
					'path'     => '/de',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
			],
		],
	],
	'polylang_shouldReturnLangsWhenDe'                                => [
		'current_lang' => 'de',
		'config'       => [
			'data'                 => $i18n_plugins['polylang'],
			'i18n_plugin'          => 'polylang',
			'rocket_has_i18n'      => 'polylang',
			'get_rocket_i18n_code' => $i18n_plugins['polylang']['codes'],
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/fr',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['polylang']['codes'],
			'get_rocket_i18n_home_url' => [
				'en' => 'http://example.org',
				'fr' => 'http://example.org/fr',
			],
			'get_rocket_parse_url'     => [
				'http://example.org'    => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
				'http://example.org/fr' => [
					'host'     => 'example.org',
					'path'     => '/fr',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
			],
		],
	],
	'polylang_shouldReturnEnAndDe'                                    => [
		'current_lang' => 'fr',
		'config'       => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected'     => [
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/',
			'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/example.org(.*)\/de',
		],
		'mocks'        => [
			'get_rocket_i18n_code'     => $i18n_plugins['polylang']['codes'],
			'get_rocket_i18n_home_url' => [
				'en' => 'http://example.org',
				'de' => 'http://example.org/de',
			],
			'get_rocket_parse_url'     => [
				'http://example.org'    => [
					'host'     => 'example.org',
					'path'     => '',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
				'http://example.org/de' => [
					'host'     => 'example.org',
					'path'     => '/de',
					'scheme'   => 'http',
					'query'    => '',
					'fragment' => '',
				],
			],
		],
	],
];
