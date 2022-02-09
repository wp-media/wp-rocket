<?php

return [
	'testShouldReturnSameWhenEmptyFonts' => [
		'config'   => [
			'donotoptimize' => false,
			'query'         => false,
		],
		'fonts'    => [],
		'expected' => [],
	],

	'testShouldReturnSameWhenRUCSSNotAllowed' => [
		'config'   => [
			'donotoptimize' => true,
			'query'         => false,
		],
		'fonts'    => [
			'wp-content/themes/twentytwenty/assets/fonts/opensans.woff2',
		],
		'expected' => [
			'wp-content/themes/twentytwenty/assets/fonts/opensans.woff2',
		],
	],
	'testShouldReturnSameWhenEmptyUsedCSS' => [
		'config'   => [
			'donotoptimize' => false,
			'query'         => false,
		],
		'fonts'    => [
			'wp-content/themes/twentytwenty/assets/fonts/opensans.woff2',
		],
		'expected' => [
			'wp-content/themes/twentytwenty/assets/fonts/opensans.woff2',
		],
	],
	'testShouldReturnUpdatedArrayWhenUsedCSS' => [
		'config'   => [
			'donotoptimize' => false,
			'query'         => [
				'url'            => 'http://example.org',
				'css' => '@font-face {
					font-family: \'Open Sans\';
					src: url(http://example.org/wp-content/themes/twentytwenty/assets/fonts/opensans.woff2) format(\'woff2\');
				  }',
				  'unprocessedcss' => wp_json_encode([]),
				  'retries'        => 1,
	'is_mobile'      => false,
			],
		],
		'fonts'    => [
			'wp-content/themes/twentytwenty/assets/fonts/roboto.woff2',
			'wp-content/themes/twentytwenty/assets/fonts/opensans.woff2',
		],
		'expected' => [
			'wp-content/themes/twentytwenty/assets/fonts/opensans.woff2',
		],
	],
];
