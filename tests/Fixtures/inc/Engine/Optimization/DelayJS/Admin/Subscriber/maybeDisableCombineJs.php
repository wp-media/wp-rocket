<?php

return [
	'testShouldReturnSameWhenOptionsNotSet' => [
		'config' => [
			'value'     => [
				'minify_js' => 0,
				'cdn'       => 0,
			],
			'old_value' => [
				'minify_js' => 1,
				'cdn'       => 0,
			],
		],
		'expected' => [
			'minify_js' => 0,
			'cdn'       => 0,
		],
	],
	'testShouldReturnSameWhenOptionsEqualZero' => [
		'config' => [
			'value'     => [
				'minify_js'             => 0,
				'minify_concatenate_js' => 0,
				'delay_js'              => 0,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 0,
				'delay_js'              => 1,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_js'             => 0,
			'minify_concatenate_js' => 0,
			'delay_js'              => 0,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnSameWhenDelayJsEnabled' => [
		'config' => [
			'value'     => [
				'minify_js'             => 0,
				'minify_concatenate_js' => 0,
				'delay_js'              => 1,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 0,
				'delay_js'              => 1,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_js'             => 0,
			'minify_concatenate_js' => 0,
			'delay_js'              => 1,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnSameWhenCombineJsEnabled' => [
		'config' => [
			'value'     => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 1,
				'delay_js'              => 0,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 0,
				'delay_js'              => 0,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_js'             => 1,
			'minify_concatenate_js' => 1,
			'delay_js'              => 0,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnUpdatedWhenCombineJsAndDelayJsEnabled' => [
		'config' => [
			'value'     => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 1,
				'delay_js'              => 1,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 0,
				'delay_js'              => 0,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_js'             => 1,
			'minify_concatenate_js' => 0,
			'delay_js'              => 1,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnUpdatedWhenCombineJsAndDelayJsEnabledBefore' => [
		'config' => [
			'value'     => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 1,
				'delay_js'              => 1,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_js'             => 1,
				'minify_concatenate_js' => 1,
				'delay_js'              => 1,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_js'             => 1,
			'minify_concatenate_js' => 0,
			'delay_js'              => 1,
			'cdn'                   => 0,
		],
	],
];
