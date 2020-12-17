<?php

return [
	'testShouldReturnOriginalWhenConstantSet' => [
		'config' => [
			'donotrocketoptimize' => true,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'          => 1,
				'minify_concatenate_js' => 1,
			],
		],
		'excluded' => [],
		'expected' => [],
	],
	'testShouldReturnOriginalWhenDisabledByPostMeta' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => true,
			'options'             => [
				'defer_all_js'          => 1,
				'minify_concatenate_js' => 1,
			],
		],
		'excluded' => [],
		'expected' => [],
	],
	'testShouldReturnOriginalWhenDeferJSDisabled' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'          => 0,
				'minify_concatenate_js' => 1,
			],
		],
		'excluded' => [],
		'expected' => [],
	],
	'testShouldReturnOriginalWhenCombineJSDisabled' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'          => 1,
				'minify_concatenate_js' => 0,
			],
		],
		'excluded' => [],
		'expected' => [],
	],
	'testShouldReturnUpdatedExcludedArray' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'          => 1,
				'minify_concatenate_js' => 1,
			],
		],
		'excluded' => [],
		'expected' => [
			'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
		],
	],
];
