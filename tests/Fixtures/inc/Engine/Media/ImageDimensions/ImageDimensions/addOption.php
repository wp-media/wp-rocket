<?php

return [
	'vfs_dir'   => '/',
	'test_data' => [
		'shouldReturnValidOptionsWithEmptyOptions' => [
			'input' => [
				'options' => [],
			],
			'expected' => [
				'image_dimensions' => 0,
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
				'image_dimensions' => 0,
			]
		],
	],
];
