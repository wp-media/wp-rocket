<?php

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
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
				]
			],
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
