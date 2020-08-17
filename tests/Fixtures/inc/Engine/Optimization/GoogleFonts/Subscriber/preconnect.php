<?php
return [
	// nowprocket query string
	'NowprocketQueryString' => [
		true,
		1,
		[],
		'preconnect',
		[],
	],
	// Optimize Google Fonts disabled.
	'OptimizeGoogleFontsDisabled' => [
		false,
		0,
		[],
		'preconnect',
		[],
	],
	// Relation type is not preconnect.
	'RelationTypeNotPreconnect' => [
		false,
		1,
		[],
		'prefetch',
		[],
	],
	// Relation type is preconnect, origin array empty.
	'RelationTypePreconnect' => [
		false,
		1,
		[],
		'preconnect',
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
];