<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'false_ShouldReturnHomeUrl'                 => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin' => false,
			'data'        => [],
		],
		'expected' => 'http://example.org',
	],
	'baz_ShouldReturnHomeUrl'                   => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin' => 'baz',
			'data'        => [
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
			'i18n_plugin' => 'wpml',
			'data'        => $i18n_plugins['wpml'],
		],
		'expected' => 'http://example.org',
	],
	'wpml_ShouldReturnFr'                       => [
		'lang'     => 'fr',
		'config'   => [
			'i18n_plugin' => 'wpml',
			'data'        => $i18n_plugins['wpml'],
		],
		'expected' => 'http://example.org?lang=fr',
	],
	'wpml_ShouldReturnDe'                       => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin' => 'wpml',
			'data'        => $i18n_plugins['wpml'],
		],
		'expected' => 'http://example.org?lang=de',
	],

	/**
	 * qTranslate
	 */
	'qtranslate_ShouldReturnDefault'            => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin' => 'qtranslate',
			'data'        => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org',
	],
	'qtranslate_ShouldReturnHomeUrlWhenNoCodes' => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin' => 'qtranslate',
			'data'        => [
				'codes' => [],
			],
		],
		'expected' => 'http://example.org',
	],
	'qtranslate_ShouldReturnEn'                 => [
		'lang'     => 'en',
		'config'   => [
			'i18n_plugin' => 'qtranslate',
			'data'        => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org',
	],
	'qtranslate_ShouldReturnFr'                 => [
		'lang'     => 'fr',
		'config'   => [
			'i18n_plugin' => 'qtranslate',
			'data'        => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org/fr',
	],
	'qtranslate_ShouldReturnDe'                 => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin' => 'qtranslate',
			'data'        => $i18n_plugins['qtranslate'],
		],
		'expected' => 'http://example.org/de',
	],

	/**
	 * polylang
	 */
	'polylang_ShouldReturnHomeUrlWhenNoCodes'   => [
		'lang'     => '',
		'config'   => [
			'i18n_plugin' => 'polylang',
			'data'        => [
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
			'i18n_plugin' => 'polylang',
			'data'        => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org',
	],
	'polylang_shouldReturnDefaultWhenEn'        => [
		'lang'     => 'en',
		'config'   => [
			'i18n_plugin' => 'polylang',
			'data'        => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org',
	],
	'polylang_shouldReturnDe'                   => [
		'lang'     => 'de',
		'config'   => [
			'i18n_plugin' => 'polylang',
			'data'        => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org/de',
	],
	'polylang_shouldReturnFr'                   => [
		'lang'     => 'fr',
		'config'   => [
			'i18n_plugin' => 'polylang',
			'data'        => $i18n_plugins['polylang'],
		],
		'expected' => 'http://example.org/fr',
	],
];
