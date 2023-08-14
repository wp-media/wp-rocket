<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'shouldReturnFalseWhenNoI18nPlugin'                => [
		'i18n_plugin' => false,
		'codes'       => [],
		'expected'    => [],
	],
	'shouldReturnFalseWhenNotWpmlQTranslateOrPolyLang' => [
		'i18n_plugin' => 'baz',
		'codes'       => [ 'de', 'en', 'fr' ],
		'expected'    => [],
	],
	'wpml_ShouldReturnLangCodes'                       => [
		'i18n_plugin' => 'wpml',
		'codes'       => $i18n_plugins['wpml']['codes'],
		'expected'    => $i18n_plugins['wpml']['langs'],
	],
	'qtranslate_ShouldReturnLangCodes'                 => [
		'i18n_plugin' => 'qtranslate',
		'codes'       => $i18n_plugins['qtranslate']['codes'],
		'expected'    => $i18n_plugins['qtranslate']['langs'],
	],
	'qtranslate_ShouldReturnEmptyArrayWhenNoCodes'     => [
		'i18n_plugin' => 'qtranslate',
		'codes'       => [],
		'expected'    => [],
	],
	'qtranslate-x_ShouldReturnLangCodes'               => [
		'i18n_plugin' => 'qtranslate-x',
		'codes'       => $i18n_plugins['qtranslate-x']['codes'],
		'expected'    => $i18n_plugins['qtranslate-x']['langs'],
	],
	'qtranslate-X_ShouldReturnEmptyArrayWhenNoCodes'   => [
		'i18n_plugin' => 'qtranslate',
		'codes'       => [],
		'expected'    => [],
	],
	'polylang_ShouldReturnEmptyArrayWhenNoCodes'       => [
		'i18n_plugin' => 'polylang',
		'codes'       => [],
		'expected'    => [],
	],
	'polylang_ShouldReturnLangCodes'                   => [
		'i18n_plugin' => 'polylang',
		'codes'       => $i18n_plugins['polylang']['codes'],
		'expected'    => $i18n_plugins['polylang']['langs'],
	],
];
