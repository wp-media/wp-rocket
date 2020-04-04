<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'false_ShouldReturnHomeUrl'                   => [
		'i18n_plugin'     => false,
		'rocket_has_i18n' => false,
		'config'          => [],
		'expected'        => [ 'http://example.org' ],
	],
	'baz_ShouldReturnHomeUrl'                     => [
		'i18n_plugin'     => 'baz',
		'rocket_has_i18n' => false,
		'config'          => [
			'codes' => [ 'de', 'en', 'fr' ],
			'langs' => [ 'de', 'en', 'fr' ],
		],
		'expected'        => [ 'http://example.org' ],
	],
	'wpml_ShouldReturnUris'                       => [
		'i18n_plugin'     => 'wpml',
		'rocket_has_i18n' => 'wpml',
		'config'          => $i18n_plugins['wpml'],
		'expected'        => [
			'http://example.org',
			'http://example.org?lang=fr',
			'http://example.org?lang=de',
		],
	],
	'qtranslate_ShouldReturnUris'                 => [
		'i18n_plugin'     => 'qtranslate',
		'rocket_has_i18n' => 'qtranslate',
		'config'          => $i18n_plugins['qtranslate'],
		'expected'        => [
			'http://example.org/en',
			'http://example.org/fr',
			'http://example.org/de',
		],
	],
	'qtranslate_ShouldReturnHomeUrlWhenNoCodes'   => [
		'i18n_plugin'     => 'qtranslate',
		'rocket_has_i18n' => 'qtranslate',
		'config'          => [
			'codes' => [],
		],
		'expected'        => [ 'http://example.org' ],
	],
	'qtranslate-x_ShouldReturnUris'               => [
		'i18n_plugin'     => 'qtranslate-x',
		'rocket_has_i18n' => 'qtranslate-x',
		'config'          => $i18n_plugins['qtranslate-x'],
		'expected'        => [
			'http://example.org/en',
			'http://example.org/fr',
			'http://example.org/de',
		],
	],
	'qtranslate-x_ShouldReturnHomeUrlWhenNoCodes' => [
		'i18n_plugin'     => 'qtranslate-x',
		'rocket_has_i18n' => 'qtranslate-x',
		'config'          => [
			'codes' => [],
		],
		'expected'        => [ 'http://example.org' ],
	],
	'polylang_ShouldReturnHomeUrlWhenNoCodes'     => [
		'i18n_plugin'     => 'polylang',
		'rocket_has_i18n' => false,
		'config'          => [
			'codes'   => [],
			'options' => [],
		],
		'expected'        => [ 'http://example.org' ],
	],
	[
		'i18n_plugin'     => 'polylang',
		'rocket_has_i18n' => 'polylang',
		'config'          => $i18n_plugins['polylang'],
		'expected'        => [
			'http://example.org',
			'http://example.org/fr',
			'http://example.org/de',
		],
	],
];
