<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/data/i18nPlugins.php';

return [
	'false_ShouldReturnHomeUrl' => [
		'i18n_plugin' => false,
		'config'      => [],
		'expected'    => [ 'http://example.org' ],
	],
	'baz_ShouldReturnHomeUrl' => [
		'i18n_plugin' => 'baz',
		'config'      => [
			'codes' => [ 'de', 'en', 'fr' ],
			'langs' => [ 'de', 'en', 'fr' ],
		],
		'expected'    => [ 'http://example.org' ],
	],
	'wpml_ShouldReturnUris' => [
		'i18n_plugin' => 'wpml',
		'config'      => $i18n_plugins['wpml'],
		'expected'    => [
			'http://example.org',
			'http://example.org?lang=fr',
			'http://example.org?lang=de',
		],
	],
	'qtranslate_ShouldReturnUris' => [
		'i18n_plugin' => 'qtranslate',
		'config'      => $i18n_plugins['qtranslate'],
		'expected'    => [
			'http://example.org/en',
			'http://example.org/fr',
			'http://example.org/de',
		],
	],
	'qtranslate_ShouldReturnHomeUrlWhenNoCodes' => [
		'i18n_plugin' => 'qtranslate',
		'config'      => [
			'codes' => [],
		],
		'expected'    => [ 'http://example.org' ],
	],
	'qtranslate-x_ShouldReturnUris' => [
		'i18n_plugin' => 'qtranslate-x',
		'config'      => $i18n_plugins['qtranslate-x'],
		'expected'    => [
			'http://example.org/en',
			'http://example.org/fr',
			'http://example.org/de',
		],
	],
	'qtranslate-x_ShouldReturnHomeUrlWhenNoCodes' => [
		'i18n_plugin' => 'qtranslate-x',
		'config'      => [
			'codes' => [],
		],
		'expected'    => [ 'http://example.org' ],
	],
	'polylang_ShouldReturnHomeUrlWhenNoCodes' => [
		'i18n_plugin' => 'polylang',
		'config'      => [
			'codes' => [],
		],
		'expected'    => [ 'http://example.org' ],
	],
//	[
//		'i18n_plugin' => 'polylang',
//		'codes'       => [ 'de', 'en', 'fr' ],
//		'expected'    => [ 'de', 'en', 'fr' ],
//	],
];
