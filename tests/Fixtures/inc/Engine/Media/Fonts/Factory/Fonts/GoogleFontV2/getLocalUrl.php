<?php

return [
	'testShouldReturnLocalUrl' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css2?family=Roboto',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/2/b7488900b01c2ffa45c4faf5876bd050.css',
	],
	'testShouldReturnLocalUrlWithVersion' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css2?family=Roboto&ver=1.0',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/2/bd7d7cead547593f5d4c8a439f32e209.css',
	],
	'testShouldReturnLocalUrlWithSubset' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css2?family=Roboto:300,400,500&subset=latin,latin-ext',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/2/b6eed853c9ca4324a5fd627ea3465c48.css',
	],
	'testShouldReturnLocalUrlWithMultipleFonts' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css2?family=Roboto|Open+Sans',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/2/3d7fe3a1467a88318d3fb6e4ced0b127.css',
	],
	'testShouldReturnLocalUrlWithMultipleFontsAndVersion' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css2?family=Roboto|Open+Sans&ver=1.0',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/2/3fbdd05b1bf26933207aeb7d3d910d30.css',
	],
];
