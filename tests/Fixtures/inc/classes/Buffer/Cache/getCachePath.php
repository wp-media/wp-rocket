<?php

return [
	'shouldNameTheFileFromThePartsThatArrived'                => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'currency' ] ],
			'cookies'         => [ 'prefs' => [ 'currency' => 'eur' ] ],
		],
		'expected' => '/tmp/cache/example.org/hello/index-eur.html',
	],
	'shouldNameTheFileFromEveryDeclaredCookie'                => [
		'config'   => [
			'dynamic_cookies' => [
				'geo'   => [ 'country' ],
				'prefs' => [ 'currency' ],
			],
			'cookies'         => [
				'geo'   => [ 'country' => 'fr' ],
				'prefs' => [ 'currency' => 'eur' ],
			],
		],
		'expected' => '/tmp/cache/example.org/hello/index-fr-eur.html',
	],
	'shouldKeepThePlaceOfAPartThatDidNotArrive'               => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'currency' ] ],
			'cookies'         => [ 'prefs' => [ 'lang' => 'fr' ] ],
		],
		'expected' => '/tmp/cache/example.org/hello/index-.html',
	],
	'shouldKeepThePlaceOfPartsOfACookieSentAsAPlainValue'     => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'currency' ] ],
			'cookies'         => [ 'prefs' => 'eur' ],
		],
		'expected' => '/tmp/cache/example.org/hello/index-.html',
	],
	'shouldKeepThePlaceOfEveryPartOfACookieSentAsAPlainValue' => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'currency', 'lang' ] ],
			'cookies'         => [ 'prefs' => 'eur' ],
		],
		'expected' => '/tmp/cache/example.org/hello/index--.html',
	],
	'shouldKeepThePlaceOfAPartNamedByDigits'                  => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ '0' ] ],
			'cookies'         => [ 'prefs' => 'eur' ],
		],
		'expected' => '/tmp/cache/example.org/hello/index-.html',
	],
	'shouldLeaveNoMarkForAFirstPartThatArrivedEmpty'          => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'a', 'b' ] ],
			'cookies'         => [
				'prefs' => [
					'a' => '',
					'b' => 'x',
				],
			],
		],
		'expected' => '/tmp/cache/example.org/hello/index-x.html',
	],
	'shouldLeaveNoMarkForASecondPartThatArrivedEmpty'         => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'a', 'b' ] ],
			'cookies'         => [
				'prefs' => [
					'a' => 'x',
					'b' => '',
				],
			],
		],
		'expected' => '/tmp/cache/example.org/hello/index-x.html',
	],
	'shouldLeaveNoMarkForACookieThatDidNotArrive'             => [
		'config'   => [
			'dynamic_cookies' => [
				'geo'   => [ 'country' ],
				'prefs' => [ 'currency' ],
			],
			'cookies'         => [ 'geo' => [ 'country' => 'fr' ] ],
		],
		'expected' => '/tmp/cache/example.org/hello/index-fr.html',
	],
	'shouldIgnoreACookieAddedToTheEnvironmentLater'           => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'currency' ] ],
			'cookies'         => [],
			'superglobal'     => [ 'prefs' => [ 'currency' => 'eur' ] ],
		],
		'expected' => '/tmp/cache/example.org/hello/index.html',
	],
	'shouldNameTheFileFromACookieTakenOutOfTheEnvironment'    => [
		'config'   => [
			'dynamic_cookies' => [ 'prefs' => [ 'currency' ] ],
			'cookies'         => [ 'prefs' => [ 'currency' => 'eur' ] ],
			'superglobal'     => [],
		],
		'expected' => '/tmp/cache/example.org/hello/index-eur.html',
	],
	'shouldNameTheFileFromACookieDeclaredBothWays'            => [
		'config'   => [
			'dynamic_cookies' => [
				0      => 'gdpr',
				'gdpr' => [ 'allowed_cookies' ],
			],
			'cookies'         => [ 'gdpr' => 'yes' ],
		],
		'expected' => '/tmp/cache/example.org/hello/index-yes-.html',
	],
];
