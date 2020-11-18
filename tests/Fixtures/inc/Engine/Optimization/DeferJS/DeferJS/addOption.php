<?php

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
		],
		'expected' => [
			'exclude_defer_js' => [],
		]
	],
	'shouldNotOverrideOtherOptions' => [
		'input' => [
			'options' => [
				'test_option' => 1,
			],
		],
		'expected' => [
			'test_option'      => 1,
			'exclude_defer_js' => [],
		]
	],
];
