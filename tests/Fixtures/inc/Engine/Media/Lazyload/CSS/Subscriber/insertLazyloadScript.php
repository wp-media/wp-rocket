<?php
return [
    'shouldDoAsExpected' => [
        'config' => [
			'is_allowed' => true,
			'WP_ROCKET_VERSION' => '1.0.0',
			'WP_ROCKET_ASSETS_JS_URL' => 'https://example.org/test/',
			'threshold' => 400,
        ],
		'expected' => [
			'url' => 'https://example.org/test/lazyload-css.js',
			'version' => '1.0.0',
			'data' => [
				'threshold' => 400
			]
		]
	],
	'noAllowedShouldBailOut' => [
		'config' => [
			'is_allowed' => false,
			'WP_ROCKET_VERSION' => '1.0.0',
			'WP_ROCKET_ASSETS_JS_URL' => 'https://example.org/test/',
			'threshold' => 400,
		],
		'expected' => [
			'url' => 'https://example.org/test/lazyload-css.js',
			'version' => '1.0.0',
			'data' => [
				'threshold' => 400
			]
		]
	]
];
