<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'false_ShouldReturnEmptyArrayWhenNoLangGiven'   => [
		'current_lang' => '',
		'config'       => [
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
			'data'            => [],
		],
		'expected'     => [],
	],
	'false_ShouldReturnEmptyArrayWhenLangNotString' => [
		'current_lang' => 10, // not string.
		'config'       => [
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
			'data'            => [],
		],
		'expected'     => [],
	],
	'false_ShouldReturnEmptyArrayWhenNoPlugin'      => [
		'current_lang' => 'en',
		'config'       => [
			'i18n_plugin'     => false,
			'rocket_has_i18n' => false,
			'data'            => [],
		],
		'expected'     => [],
	],
	'false_ShouldReturnEmptyArrayWhenNoPlugin'      => [
		'current_lang' => 'en',
		'config'       => [
			'i18n_plugin'     => 'baz',
			'rocket_has_i18n' => false,
			'data'            => [
				'codes' => [ 'de', 'en', 'fr' ],
				'langs' => [ 'de', 'en', 'fr' ],
			],
		],
		'expected'     => [],
	],

	/**
	 * WPML
	 */
	'wpml_ShouldReturnDefault'                      => [
		'current_lang' => 'en',
		'config'       => [
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
			'data'            => $i18n_plugins['wpml'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
		],
	],
	'wpml_ShouldReturnFr'                           => [
		'current_lang' => 'fr',
		'config'       => [
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
			'data'            => $i18n_plugins['wpml'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
		],
	],
	'wpml_ShouldReturnDe'                           => [
		'current_lang' => 'de',
		'config'       => [
			'i18n_plugin'     => 'wpml',
			'rocket_has_i18n' => 'wpml',
			'data'            => $i18n_plugins['wpml'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/',
		],
	],

	/**
	 * qTranslate
	 */
	'qtranslate_ShouldReturnEmptyArray'             => [
		'current_lang' => '',
		'config'       => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected'     => [],
	],
	'qtranslate_ShouldReturnEmptyArrayWhenNoCodes'  => [
		'current_lang' => 'de',
		'config'       => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => [
				'codes' => [],
			],
		],
		'expected'     => [],
	],
	'qtranslate_ShouldReturnEn'                     => [
		'current_lang' => 'fr',
		'config'       => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/en',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
		],
	],
	'qtranslate_ShouldReturnFr'                     => [
		'current_lang' => 'en',
		'config'       => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
		],
	],
	'qtranslate_ShouldReturnDe'                 => [
		'current_lang'     => 'de',
		'config'   => [
			'i18n_plugin'     => 'qtranslate',
			'rocket_has_i18n' => 'qtranslate',
			'data'            => $i18n_plugins['qtranslate'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/en',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
		],
	],

	/**
	 * polylang
	 */
	'polylang_ShouldReturnEmptyArrayWhenNoCodes'   => [
		'current_lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => false,
			'data'            => [
				'codes'   => [],
				'langs'   => [],
				'options' => [],
			],
		],
		'expected' => [],
	],
	'polylang_ShouldReturnEmptyArrayWhenNoCodesAndPolylangConfigured'              => [
		'current_lang'     => '',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected' => [],
	],
	'polylang_shouldReturnDefaultWhenEn'              => [
		'current_lang'     => 'en',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
		],
	],
	'polylang_shouldReturnDe'                   => [
		'current_lang'     => 'de',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/en',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
		],
	],
	'polylang_shouldReturnFr'                   => [
		'current_lang'     => 'fr',
		'config'   => [
			'i18n_plugin'     => 'polylang',
			'rocket_has_i18n' => 'polylang',
			'data'            => $i18n_plugins['polylang'],
		],
		'expected'     => [
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/en',
			'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
		],
	],
];
