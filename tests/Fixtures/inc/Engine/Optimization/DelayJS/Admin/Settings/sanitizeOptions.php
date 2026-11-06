<?php

return [
	'testShouldSetDefaultValueIfNotSet' => [
		'config'    => [
			'input'           => [],
			'sanitized_input' => [
				'delay_js'            => 0,
				'delay_js_exclusions' => null,
				'delay_js_execution_safe_mode' => 0,
			],
		],
		'expected' => [
			'delay_js'            => 0,
			'delay_js_execution_safe_mode' => 0,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldSetCorrectValueIfDifferentType' => [
		'config'   => [
			'input'           => [
				'delay_js'            => true,
				'delay_js_exclusions' => "wp-content/themes/twentytwenty/script.js\n<script>\nGoogleAnalytics\ngtm\nwp-includes/.*.js",
				'delay_js_exclusions_selected' => [],
    			'delay_js_exclusions_selected_exclusions' => [],
				'delay_js_execution_safe_mode' => 0,
			],
			'sanitized_input' => [
				'delay_js'            => 1,
				'delay_js_exclusions' => [
					'wp-content/themes/twentytwenty/script.js',
					'GoogleAnalytics',
					'gtm',
					'wp-includes/(.*).js',
				],
				'delay_js_exclusions_selected' => [],
				'delay_js_exclusions_selected_exclusions' => [],
				'delay_js_execution_safe_mode' => 0,
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
			'delay_js_exclusions_selected' => [],
			'delay_js_exclusions_selected_exclusions' => [],
			'delay_js_execution_safe_mode' => 0,
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
				'delay_js_exclusions_selected' => [],
				'delay_js_exclusions_selected_exclusions' => [],
				'delay_js_execution_safe_mode' => 1,
			],
			'sanitized_input' => [
				'delay_js'            => 1,
				'delay_js_exclusions' => [
					'wp-content/themes/twentytwenty/script.js',
					'GoogleAnalytics',
					'gtm',
					'wp-includes/(.*).js',
				],
				'delay_js_exclusions_selected' => [],
				'delay_js_exclusions_selected_exclusions' => [],
				'delay_js_execution_safe_mode' => 1,
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
			'delay_js_exclusions_selected' => [],
			'delay_js_exclusions_selected_exclusions' => [],
			'delay_js_execution_safe_mode' => 1,
		],
	],
];
