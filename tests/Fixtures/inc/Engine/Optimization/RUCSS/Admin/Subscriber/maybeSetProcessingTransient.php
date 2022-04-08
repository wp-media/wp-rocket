<?php

return [
	'shouldDoNothingWhenRUCSSNotSet' => [
		'input' => [
			'old_value' => [],
			'value'     => [],
		],
		'expected' => false,
	],
	'shouldDoNothingWhenRUCSSDisabled' => [
		'input' => [
			'old_value' => [
				'remove_unused_css' => 0,
			],
			'value'     => [
				'remove_unused_css' => 0,
			],
		],
		'expected' => false,
	],
	'shouldDoNothingWhenOptionDidntChange' => [
		'input' => [
			'old_value' => [
				'remove_unused_css' => 1,
			],
			'value'     => [
				'remove_unused_css' => 1,
			],
		],
		'expected' => false,
	],
	'shouldSetTransientWhenRUCSSEnabled' => [
		'input' => [
			'old_value' => [
				'remove_unused_css' => 0,
			],
			'value'     => [
				'remove_unused_css' => 1,
			],
		],
		'expected' => true,
	],
];
