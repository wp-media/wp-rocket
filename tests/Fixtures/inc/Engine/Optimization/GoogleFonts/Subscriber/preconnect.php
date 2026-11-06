<?php
return [
	// nowprocket query string
	'NowprocketQueryString' => [
		true,
		1,
		[],
		'preconnect',
		false,
		0,
		[],
	],
	// Optimize Google Fonts disabled.
	'OptimizeGoogleFontsDisabled' => [
		false,
		0,
		[],
		'preconnect',
		false,
		0,
		[],
	],
	// Relation type is not preconnect.
	'RelationTypeNotPreconnect' => [
		false,
		1,
		[],
		'prefetch',
		false,
		0,
		[],
	],
	// Relation type is preconnect, origin array empty.
	'RelationTypePreconnect' => [
		false,
		1,
		[],
		'preconnect',
		false,
		0,
		[
			[
				'href' => 'https://fonts.gstatic.com',
				1      => 'crossorigin',
			],
		],
	],
	// Relation type is preconnect, origin array has values.
	'RelationTypePreconnectAndValues' => [
		false,
		1,
		[
			[
				'href' => 'https://123456.rocketcdn.me',
				1      => 'crossorigin',
			],
		],
		'preconnect',
		false,
		0,
		[
			[
				'href' => 'https://123456.rocketcdn.me',
				1      => 'crossorigin',
			],
			[
				'href' => 'https://fonts.gstatic.com',
				1      => 'crossorigin',
			],
		],
	],
	'UserLoggedInCacheLoggedUserDisabled' => [
		false,
		1,
		[],
		'preconnect',
		true,
		0,
		[],
	],
	'UserLoggedInCacheLoggedUserEnabled' => [
		false,
		1,
		[],
		'preconnect',
		true,
		1,
		[
			[
				'href' => 'https://fonts.gstatic.com',
				1      => 'crossorigin',
			],
		],
	],
	'UserNotLoggedIn' => [
		false,
		1,
		[],
		'preconnect',
		false,
		0,
		[
			[
				'href' => 'https://fonts.gstatic.com',
				1      => 'crossorigin',
			],
		],
	]
];