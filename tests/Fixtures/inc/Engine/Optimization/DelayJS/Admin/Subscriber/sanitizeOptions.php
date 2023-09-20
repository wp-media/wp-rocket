<?php

return [
	'testShouldSetDefaultValueIfNotSet' => [
		'config'    => [
			'input'           => [],
			'sanitized_input' => [
				'delay_js'            => 0,
				'delay_js_exclusions' => null
			],
		],
		'expected' => [
			'delay_js'            => 0,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldSetCorrectValueIfDifferentType' => [
		'config'   => [
			'input'           => [
				'delay_js'            => true,
				'delay_js_exclusions' => "wp-content/themes/twentytwenty/script.js\n<script>\nGoogleAnalytics\ngtm\nwp-includes/.*.js",
			],
			'sanitized_input' => [
				'delay_js'            => 1,
				'delay_js_exclusions' => [
					'wp-content/themes/twentytwenty/script.js',
					'GoogleAnalytics',
					'gtm',
					'wp-includes/(.*).js',
				],
			],
		],
		'expected' => [
			'delay_js'            => 1,
			'delay_js_exclusions' => [
				'wp-content/themes/twentytwenty/script.js',
				'GoogleAnalytics',
				'gtm',
				'wp-includes/(.*).js',
			],
		],
	],
	'testShouldPreserveValueIfCorrectType' => [
		'config'   => [
			'input'           => [
				'delay_js'            => 1,
				'delay_js_exclusions' => [
					'wp-content/themes/twentytwenty/script.js',
					'GoogleAnalytics',
					'<script>',
					'gtm',
					'wp-includes/.*.js'
				],
			],
			'sanitized_input' => [
				'delay_js'            => 1,
				'delay_js_exclusions' => [
					'wp-content/themes/twentytwenty/script.js',
					'GoogleAnalytics',
					'gtm',
					'wp-includes/(.*).js',
				],
			],
		],
		'expected' => [
			'delay_js'          => 1,
			'delay_js_exclusions' => [
				'wp-content/themes/twentytwenty/script.js',
				'GoogleAnalytics',
				'gtm',
				'wp-includes/(.*).js',
			],
		],
	],
];
