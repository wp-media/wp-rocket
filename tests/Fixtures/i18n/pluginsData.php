<?php

return [
	'wpml' => [
		'codes'        => [
			'en' => [
				'code'           => 'en',
				'id'             => '1',
				'english_name'   => 'English',
				'native_name'    => 'English',
				'major'          => '1',
				'active'         => '1',
				'default_locale' => 'en_US',
				'encode_url'     => '0',
				'tag'            => 'en-US',
				'display_name'   => 'English',
			],
			'fr' => [
				'code'           => 'fr',
				'id'             => '4',
				'english_name'   => 'French',
				'native_name'    => 'Français',
				'major'          => '1',
				'active'         => '1',
				'default_locale' => 'fr_FR',
				'encode_url'     => '0',
				'tag'            => 'fr-FR',
				'display_name'   => 'French',
			],
			'de' => [
				'code'           => 'de',
				'id'             => '3',
				'english_name'   => 'German',
				'native_name'    => 'Deutsch',
				'major'          => '1',
				'active'         => '1',
				'default_locale' => 'de_DE',
				'encode_url'     => '0',
				'tag'            => 'de-DE',
				'display_name'   => 'German',
			],
		],
		'default_lang' => 'en',
		'langs'        => [ 'en', 'fr', 'de' ],
		'uris'         => [
			'en' => '',
			'fr' => '?lang=fr',
			'de' => '?lang=de',
		],
	],

	'qtranslate' => [
		'default_lang' => 'en',
		'codes'        => [ 'en', 'fr', 'de' ],
		'langs'        => [ 'en', 'fr', 'de' ],
	],

	'qtranslate-x' => [
		'default_lang' => 'en',
		'codes'        => [ 'en', 'fr', 'de' ],
		'langs'        => [ 'en', 'fr', 'de' ],
	],

	'polylang' => [
		'options'      => [
			'model'      => [
				[
					'slug'   => 'en',
					'locale' => 'en_US',
					'url'    => 'http://example.org',
				],
				[
					'slug'   => 'fr',
					'locale' => 'fr_FR',
					'url'    => 'http://example.org/fr',
				],
				[
					'slug'   => 'de',
					'locale' => 'de_DE',
					'url'    => 'http://example.org/de',
				],
			],
			'force_lang' => true,
		],
		'default_lang' => 'en',
		'codes'        => [ 'en', 'fr', 'de' ],
		'langs'        => [ 'en', 'fr', 'de' ],
	],
];
