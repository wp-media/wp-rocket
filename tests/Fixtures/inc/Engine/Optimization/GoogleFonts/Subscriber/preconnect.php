<?php
return [
	// Optimize Google Fonts disabled.
	'OptimizeGoogleFontsDisabled' => [
		0,
		[],
		'preconnect',
		[],
	],
	// Relation type is not preconnect.
	'RelationTypeNotPreconnect' => [
		1,
		[],
		'prefetch',
		[],
	],
	// Relation type is preconnect, origin array empty.
	'RelationTypePreconnect' => [
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