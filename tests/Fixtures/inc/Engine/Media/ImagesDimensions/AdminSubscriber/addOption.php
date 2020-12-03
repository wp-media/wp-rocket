<?php

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
		],
		'expected' => [
			'images_dimensions' => 0,
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
			'images_dimensions' => 0,
		]
	],
];
