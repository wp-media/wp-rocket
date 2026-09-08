<?php
$rocket_fixture_object = new stdClass();

return [
	// A site that varies the cache by parts of more than one cookie: the file name is built from
	// every definition, so every definition has to come back.
	'shouldKeepEveryNestedDefinition'            => [
		'config'   => [
			'filter' => [
				'session_id',
				'geo'   => [ 'country' ],
				'prefs' => [ 'currency' ],
			],
		],
		'expected' => [
			'session_id',
			'geo'   => [ 'country' ],
			'prefs' => [ 'currency' ],
		],
	],
	// Two cookies read for the same part of themselves are still two cookies.
	'shouldKeepCookiesThatNameTheSamePart'       => [
		'config'   => [
			'filter' => [
				'pll_language'             => [ 'lang' ],
				'wp-wpml_current_language' => [ 'lang' ],
			],
		],
		'expected' => [
			'pll_language'             => [ 'lang' ],
			'wp-wpml_current_language' => [ 'lang' ],
		],
	],
	// The file name is built in this order, so the order has to survive.
	'shouldKeepTheOrderTheListCameIn'            => [
		'config'   => [
			'filter' => [
				'session_id',
				'geo' => [ 'country' ],
				'currency',
			],
		],
		'expected' => [
			0     => 'session_id',
			'geo' => [ 'country' ],
			1     => 'currency',
		],
	],
	'shouldDropARepeatedName'                    => [
		'config'   => [
			'filter' => [ 'session_id', 'session_id', 'currency' ],
		],
		'expected' => [
			0 => 'session_id',
			2 => 'currency',
		],
	],
	'shouldDropEmptyEntries'                     => [
		'config'   => [
			'filter' => [ 'session_id', '', null, 0 ],
		],
		'expected' => [ 0 => 'session_id' ],
	],
	// The same cookie given both ways is two entries, as it has always been: one says the whole cookie
	// varies the cache, the other says which parts of it do.
	'shouldKeepANameAndADeclarationOfIt'         => [
		'config'   => [
			'filter' => [
				'gdpr',
				'gdpr' => [ 'allowed_cookies' ],
			],
		],
		'expected' => [
			0      => 'gdpr',
			'gdpr' => [ 'allowed_cookies' ],
		],
	],
	// A cookie named by digits is a cookie: PHP holds that name as an integer key, on the declaration
	// and on the request alike, so both of these are looked up and both are kept.
	'shouldKeepPartsOfCookiesNamedByDigits'      => [
		'config'   => [
			'filter' => [
				'7' => [ 'country' ],
				'9' => [ 'currency' ],
			],
		],
		'expected' => [
			7 => [ 'country' ],
			9 => [ 'currency' ],
		],
	],
	// Dropped because it names no cookie. That it also keeps a value var_export() cannot write back
	// out of the config file is what the drop is worth, not what decides it.
	'shouldDropAnEntryThatIsNeitherNameNorParts' => [
		'config'   => [
			'filter' => [
				'session_id',
				'geo' => $rocket_fixture_object,
			],
		],
		'expected' => [ 0 => 'session_id' ],
	],
	// Two names PHP reads as the same number, kept apart by comparing them as strings, which is what
	// array_unique() does on its own and what a sort flag would undo.
	'shouldKeepNamesThatOnlyLookNumeric'         => [
		'config'   => [
			'filter' => [ '1e2', '100' ],
		],
		'expected' => [ '1e2', '100' ],
	],
];
