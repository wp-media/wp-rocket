<?php
return [
	// nowprocket query string
	'NowprocketQueryString' => [
		true,
		1,
		[],
		'preconnect',
		false,
		[],
	],
	// Optimize Google Fonts disabled.
	'OptimizeGoogleFontsDisabled' => [
		false,
		0,
		[],
		'preconnect',
		false,
		[],
	],
	// Relation type is not preconnect.
	'RelationTypeNotPreconnect' => [
		false,
		1,
		[],
		'prefetch',
		false,
		[],
	],
	// Relation type is preconnect, origin array empty.
	'RelationTypePreconnect' => [
		false,
		1,
		[],
		'preconnect',
		false,
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
	'UserLoggedIn' => [
		false,
		1,
		[],
		'preconnect',
		true,
		[],
	],
	'UserNotLoggedIn' => [
		false,
		1,
		[],
		'preconnect',
		false,
		[
			[
				'href' => 'https://fonts.gstatic.com',
				1      => 'crossorigin',
			],
		],
	]
];