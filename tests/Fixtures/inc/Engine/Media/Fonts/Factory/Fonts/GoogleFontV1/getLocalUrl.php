<?php

return [
	'testShouldReturnLocalUrl' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css?family=Roboto',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/1/ebc173c0fc97eef86a6e51ada56c5a9a.css',
	],
	'testShouldReturnLocalUrlWithVersion' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css?family=Roboto&ver=1.0',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/1/130f702787108e593481b804d049b815.css',
	],
	'testShouldReturnLocalUrlWithSubset' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500&subset=latin,latin-ext',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/1/4942a9bb0eeef783d471f016bb8eba76.css',
	],
	'testShouldReturnLocalUrlWithMultipleFonts' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css?family=Roboto|Open+Sans',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/1/624f2c2b9858423d0688793189f6e6cb.css',
	],
	'testShouldReturnLocalUrlWithMultipleFontsAndVersion' => [
		'config' => [
			'font_url' => 'https://fonts.googleapis.com/css?family=Roboto|Open+Sans&ver=1.0',
		],
		'expected' => '/wp-content/cache/wp-rocket/fonts/google-fonts/1/6447d15db96450edb62158b00cabac8f.css',
	],
];
