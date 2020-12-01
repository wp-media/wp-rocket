<?php

return [
	'testShouldDoNothingWhenAbove38' => [
		'config' => [
			'options' => [
				'defer_all_js_safe' => 0,
			],
			'old_version' => '3.8',
		],
		'expected' => [
			'defer_all_js_safe' => 0,
		],
	],
	'testShouldDoNothingWhenSafeModeDisabled' => [
		'config' => [
			'options' => [
				'defer_all_js_safe' => 0,
			],
			'old_version' => '3.7.6',
		],
		'expected' => [
			'defer_all_js_safe' => 0,
		],
	],
	'testShouldUpdateOptionWhenSafeModeEnabled' => [
		'config' => [
			'options' => [
				'defer_all_js_safe' => 1,
			],
			'old_version' => '3.7.6',
		],
		'expected' => [
			'defer_all_js_safe' => 1,
			'exclude_defer_js'  => [
				'/jquery-*[0-9.]*(.min|.slim|.slim.min)*.js',
			],
		],
	],
];
