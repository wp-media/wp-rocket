<?php

return [
	'testShouldNotUpdateOptionWhenVersionAbove3.9' => [
		'options'       => [
			'delay_js'            => 1,
			'delay_js_exclusions' => [],
		],
		'old_version'   => '3.10',
		'expected'      => [
			'delay_js'            => 1,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldNotUpdateOptionWhenDelayJSEquals0'  => [
		'options'       => [
			'delay_js' => 0,
		],
		'old_version'   => '3.8',
		'expected'      => [
			'delay_js'            => 0,
			'delay_js_exclusions' => [],
		],
	],
	'testShouldUpdateOptionWhenVersionBelow3.9AndDelayJSEquals1' => [
		'options'       => [
			'delay_js'              => 1,
			'minify_concatenate_js' => 1,
		],
		'old_version'   => '3.8',
		'expected'      => [
			'delay_js'              => 1,
			'minify_concatenate_js' => 0,
			'delay_js_exclusions'   => [],
		],
	],
];
