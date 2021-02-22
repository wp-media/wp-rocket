<?php

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
		],
		'expected' => [
			'exclude_lazyload' => [],
		]
	],
	'shouldNotOverrideOtherOptions' => [
		'input' => [
			'options' => [
				'test_option' => 1,
			],
		],
		'expected' => [
			'test_option'       => 1,
			'exclude_lazyload' => [],
		]
	],
];
