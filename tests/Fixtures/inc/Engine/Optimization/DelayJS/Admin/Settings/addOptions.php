<?php

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
			'content_url' => 'https://example.org/wp-content/',
			'includes_url' => 'https://example.org/wp-includes/',
		],
		'expected' => [
			'delay_js'            => 1,
			'delay_js_exclusions' => [
				'(?:/wp-content/|/wp-includes/)(.*)',
				'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
				'js-(before|after)',
			],
		]
	],
	'shouldReturnValidOptionsWithOptionsNotArray' => [
		'input' => [
			'options' => 'test_option',
			'content_url' => 'https://example.org/wp-content/',
			'includes_url' => 'https://example.org/wp-includes/',
		],
		'expected' => [
			'test_option',
			'delay_js'            => 1,
			'delay_js_exclusions' => [
				'(?:/wp-content/|/wp-includes/)(.*)',
				'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
				'js-(before|after)',
			],
		]
	],
	'shouldOverrideOptions' => [
		'input' => [
			'options' => [
				'delay_js'            => 0,
				'delay_js_exclusions' => [
					'any value'
				],
			],
			'content_url' => 'https://example.org/wp-content/',
			'includes_url' => 'https://example.org/wp-includes/',
		],
		'expected' => [
			'delay_js'            => 1,
			'delay_js_exclusions' => [
				'(?:/wp-content/|/wp-includes/)(.*)',
				'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
				'js-(before|after)',
			],
		]
	],
	'shouldNotOverrideOtherOptions' => [
		'input' => [
			'options' => [
				'test_option'      => 1,
				'delay_js'         => 0,
				'delay_js_exclusions' => [
					'any value'
				]
			],
			'content_url' => 'https://example.org/wp-content/',
			'includes_url' => 'https://example.org/wp-includes/',
		],
		'expected' => [
			'test_option'         => 1,
			'delay_js'            => 1,
			'delay_js_exclusions' => [
				'(?:/wp-content/|/wp-includes/)(.*)',
				'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
				'js-(before|after)',
			],
		]
	],
];
