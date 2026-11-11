<?php
$default_list = [];

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
		'expected' => $default_list,
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
		'expected' => $default_list,
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
		'expected' => $default_list,
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
		'expected' => $default_list,
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
		'expected' => array_merge( [ '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js', ], $default_list ),
	],
];
