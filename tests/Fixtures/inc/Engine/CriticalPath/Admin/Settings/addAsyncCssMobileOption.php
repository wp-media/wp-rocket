<?php

return [
	'testShouldAddOptionWhenString' => [
		'options'  => 'test',
		'expected' => [
			0                  => 'test',
			'async_css_mobile' => 1,
		],
	],
	'testShouldAddOptionWhenEmptyArray' => [
		'options'  => [],
		'expected' => [
			'async_css_mobile' => 1,
		],
	],
	'testShouldAddOptionWhenNotEmptyArray' => [
		'options'  => [
			'async_css'  => 0,
			'minify_css' => 1,
		],
		'expected' => [
			'async_css'        => 0,
			'minify_css'       => 1,
			'async_css_mobile' => 1,
		],
	],
];
