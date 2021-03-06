<?php

return [
	'test_data' => [
		'testShouldAddOptionWhenString' => [
			'options'  => 'test',
			'expected' => [
				0               => 'test',
				'preload_links' => 1,
			],
		],
		'testShouldAddOptionWhenEmptyArray' => [
			'options'  => [],
			'expected' => [
				'preload_links' => 1,
			],
		],
		'testShouldAddOptionWhenNotEmptyArray' => [
			'options'  => [
				'async_css'  => 0,
				'minify_css' => 1,
			],
			'expected' => [
				'async_css'     => 0,
				'minify_css'    => 1,
				'preload_links' => 1,
			],
		],
	],
];
