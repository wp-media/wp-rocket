<?php

return [
	'testShouldDoNothingWhenVersionAbove3191' => [
		'config' => [
			'new' => '3.19.3',
			'old' => '3.19.2',
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
	'testShouldDoNothingIfPreloadFontsValueIsEmpty' => [
		'config' => [
			'new' => '3.19.1',
			'old' => '3.19',
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
	'testShouldDeletePreloadFontsValueWhenNotEmptyAndVersionUnder3191' => [
		'config' => [
			'new' => '3.19.1',
			'old' => '3.19',
			'options' => [
				'preload_fonts' => [
					'/wp-content/themes/twentytwenty/fonts/twentytwenty-icons.woff2',
                    '/wp-content/themes/twentytwenty/fonts/twentytwenty-icons.woff',
                ],
			]
		],
		'expected' => [
			'options' => [
				'preload_fonts' => []
			]
		],
	],
];
