<?php

return [
	[
		'i18n_plugin' => false,
		'codes'       => [],
		'expected'    => false,
	],
	[
		'i18n_plugin' => 'baz',
		'codes'       => [ 'de', 'en', 'fr' ],
		'expected'    => false,
	],
	[
		'i18n_plugin' => 'bar',
		'codes'       => [ 'de', 'en', 'fr' ],
		'expected'    => false,
	],
	[
		'i18n_plugin' => 'wpml',
		'codes'       => [
			'de' => 'de_DE',
			'en' => 'en_US',
			'fr' => 'fr_FR',
		],
		'expected'    => [ 'de', 'en', 'fr' ],
	],
	[
		'i18n_plugin' => 'qtranslate',
		'codes'       => [ 'de', 'en', 'fr' ],
		'expected'    => [ 'de', 'en', 'fr' ],
	],
	[
		'i18n_plugin' => 'qtranslate',
		'codes'       => [],
		'expected'    => [],
	],
	[
		'i18n_plugin' => 'qtranslate-x',
		'codes'       => [ 'de', 'en', 'fr' ],
		'expected'    => [ 'de', 'en', 'fr' ],
	],
	[
		'i18n_plugin' => 'polylang',
		'codes'       => [],
		'expected'    => [],
	],
	[
		'i18n_plugin' => 'polylang',
		'codes'       => [ 'de', 'en', 'fr' ],
		'expected'    => [ 'de', 'en', 'fr' ],
	],
];
