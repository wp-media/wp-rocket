<?php

return [
	'testShouldReturnListEnumWhenReadOnlyOptionIsMidList'      => [
		'config'   => [
			'allowed_keys' => [ 'cache_webp', 'cdn_state', 'minify_css' ],
		],
		'expected' => [
			'enum'      => [ 'cache_webp', 'minify_css' ],
			'enum_json' => '["cache_webp","minify_css"]',
		],
	],

	'testShouldReturnListEnumWhenAllowlistHasNonSequentialKeys' => [
		'config'   => [
			'allowed_keys' => [
				3 => 'cache_webp',
				7 => 'minify_css',
			],
		],
		'expected' => [
			'enum'      => [ 'cache_webp', 'minify_css' ],
			'enum_json' => '["cache_webp","minify_css"]',
		],
	],

	'testShouldReturnListEnumWhenReadOnlyOptionIsAbsent'       => [
		'config'   => [
			'allowed_keys' => [ 'cache_webp', 'minify_css' ],
		],
		'expected' => [
			'enum'      => [ 'cache_webp', 'minify_css' ],
			'enum_json' => '["cache_webp","minify_css"]',
		],
	],
];
