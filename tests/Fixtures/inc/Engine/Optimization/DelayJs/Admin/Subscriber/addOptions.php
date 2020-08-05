<?php
return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_scripts' => []
		]
	],
	'shouldReturnValidOptionsWithOptionsNotArray' => [
		'input' => [
			'options' => 'test_option',
		],
		'expected' => [
			'test_option',
			'delay_js' => 1,
			'delay_js_scripts' => []
		]
	],
	'shouldOverrideOptions' => [
		'input' => [
			'options' => [
				'delay_js' => 0,
				'delay_js_scripts' => [
					'any value'
				]
			],
		],
		'expected' => [
			'delay_js' => 1,
			'delay_js_scripts' => []
		]
	],
	'shouldNotOverrideOtherOptions' => [
		'input' => [
			'options' => [
				'test_option' => 1,
				'delay_js' => 0,
				'delay_js_scripts' => [
					'any value'
				]
			],
		],
		'expected' => [
			'test_option' => 1,
			'delay_js' => 1,
			'delay_js_scripts' => []
		]
	],
];
