<?php

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
		],
		'expected' => [
			'remove_unused_css'          => 0,
			'remove_unused_css_safelist' => [],
		]
	],
	'shouldReturnValidOptionsWithOptionsNotArray' => [
		'input' => [
			'options' => 'test_option',
		],
		'expected' => [
			'test_option',
			'remove_unused_css'         => 0,
			'remove_unused_css_safelist' => [],
		]
	],
	'shouldOverrideOptions' => [
		'input' => [
			'options' => [
				'remove_unused_css'         => 1,
				'remove_unused_css_safelist' => [
					'any value'
				]
			],
		],
		'expected' => [
			'remove_unused_css'         => 0,
			'remove_unused_css_safelist' => [],
		]
	],
	'shouldNotOverrideOtherOptions' => [
		'input' => [
			'options' => [
				'test_option'      => 1,
				'remove_unused_css'         => 1,
				'remove_unused_css_safelist' => [
					'any value'
				]
			],
		],
		'expected' => [
			'test_option'      => 1,
			'remove_unused_css'         => 0,
			'remove_unused_css_safelist' => [],
		]
	],
];
