<?php
return [
    'shouldDoAsExpected' => [
        'config' => [
			'is_allowed' => true,
			'WP_ROCKET_VERSION' => '1.0.0',
			'WP_ROCKET_ASSETS_JS_URL' => 'https://example.org/test/',
			'WP_ROCKET_ASSETS_JS_PATH' => '/path/test/',
			'path' => '/path/test/lazyload-css.min.js',
			'threshold' => 400,
			'script_data' => 'script_data',
			'exists' => true,
        ],
		'expected' => [
			'url' => 'https://example.org/test/lazyload-css.min.js',
			'path' => '/path/test/lazyload-css.min.js',
			'version' => '1.0.0',
			'script_data' => 'script_data',
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
			'path' => '/path/test/lazyload-css.min.js',
			'WP_ROCKET_ASSETS_JS_PATH' => '/path/test/',
			'threshold' => 400,
			'script_data' => 'script_data',
			'exists' => true,
		],
		'expected' => [
			'url' => 'https://example.org/test/lazyload-css.min.js',
			'path' => '/path/test/lazyload-css.min.js',
			'version' => '1.0.0',
			'script_data' => 'script_data',
			'data' => [
				'threshold' => 400
			]
		]
	]
];
