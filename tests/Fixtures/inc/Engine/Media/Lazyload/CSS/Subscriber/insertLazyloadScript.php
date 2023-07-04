<?php
return [
    'shouldDoAsExpected' => [
        'config' => [
			'WP_ROCKET_VERSION' => '1.0.0',
			'WP_ROCKET_ASSETS_JS_URL' => 'https://example.org/test/',
        ],
		'expected' => [
			'url' => 'https://example.org/test/lazyload-css.js',
			'version' => '1.0.0',
		]
	],

];
