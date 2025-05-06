<?php

return [
	'testShouldDoNothingWhenVersionAbove319' => [
		'config' => [
			'new' => '3.20',
			'old' => '3.19',
			'options' => [
				'preload_fonts' => []
			]
		],
		'expected' => [
			'options' => [
				'preload_fonts' => []
			]
		],
	],
	'testShouldUpdateOptionWhenVersionUnder319' => [
		'config' => [
			'new' => '3.19',
			'old' => '3.18',
			'options' => [
				'preload_fonts' => []
			]
		],
		'expected' => [
			'options' => [
				'preload_fonts' => [],
			],
		],
	],
	'testShouldNotEnableOptionWhenVersionUnder319' => [
		'config' => [
			'new' => '3.19',
			'old' => '3.18',
			'options' => [
				'preload_fonts' => [
					'fonts1'
				]
			]
		],
		'expected' => [
			'options' => [
				'preload_fonts' => [
					'fonts1'
				],
				'auto_preload_fonts' => true
			]
		],
	],
];
