<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'false_ShouldReturnHomeUrl'                 => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
			'data'            => [],
		],
		'expected' => 'http://example.org',
	],
	'baz_ShouldReturnHomeUrl'                   => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'baz',
			'rocket_has_i18n' => false,
			'data'            => [
				'codes' => [ 'de', 'en', 'fr' ],
				'langs' => [ 'de', 'en', 'fr' ],
			],
		],
		'expected' => 'http://example.org',
	],

	/**
	 * WPML
	 */
	'wpml_ShouldReturnDefault'                  => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
			'data'            => $i18n_plugins['wpml'],
		],
		'expected' => 'http://example.org',
	],
	'wpml_ShouldReturnFr'                       => [
		'lang'     => 'fr',
		'config'   => [
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
			'data'            => $i18n_plugins['wpml'],
		],
		'expected' => 'http://example.org?lang=fr',
	],
	'wpml_ShouldReturnDe'                       => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
			'data'            => $i18n_plugins['wpml'],
		],
		'expected' => 'http://example.org?lang=de',
	],

	/**
	 * qTranslate
	 */
	'qtranslate_ShouldReturnDefault'            => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org',
	],
	'qtranslate_ShouldReturnHomeUrlWhenNoCodes' => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => [
				'codes' => [],
			],
		],
		'expected' => 'http://example.org',
	],
	'qtranslate_ShouldReturnEn'                 => [
		'lang'     => 'en',
		'config'   => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org/en',
	],
	'qtranslate_ShouldReturnFr'                 => [
		'lang'     => 'fr',
		'config'   => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org/fr',
	],
	'qtranslate_ShouldReturnDe'                 => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org/de',
	],

	/**
	 * polylang
	 */
	'polylang_ShouldReturnHomeUrlWhenNoCodes'   => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => false,
			'data'            => [
				'codes'   => [],
				'langs'   => [],
				'options' => [],
			],
		],
		'expected' => 'http://example.org',
	],
	'polylang_shouldReturnDefault'              => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org',
	],
	'polylang_shouldReturnDefaultWhenEn'              => [
		'lang'     => 'en',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org/en',
	],
	'polylang_shouldReturnDe'                   => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org/de',
	],
	'polylang_shouldReturnFr'                   => [
		'lang'     => 'fr',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org/fr',
	],
];
