<?php

return [
	'testShouldReturnSameWhenOptionsNotSet' => [
		'config' => [
			'value'     => [
				'minify_css' => 0,
				'cdn'       => 0,
			],
			'old_value' => [
				'minify_css' => 1,
				'cdn'       => 0,
			],
		],
		'expected' => [
			'minify_css' => 0,
			'cdn'       => 0,
		],
	],
	'testShouldReturnSameWhenOptionsEqualZero' => [
		'config' => [
			'value'     => [
				'minify_css'             => 0,
				'minify_concatenate_css' => 0,
				'remove_unused_css'      => 0,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 0,
				'remove_unused_css'      => 1,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_css'             => 0,
			'minify_concatenate_css' => 0,
			'remove_unused_css'      => 0,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnSameWhenRUCSSEnabled' => [
		'config' => [
			'value'     => [
				'minify_css'             => 0,
				'minify_concatenate_css' => 0,
				'remove_unused_css'      => 1,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 0,
				'remove_unused_css'      => 1,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_css'             => 0,
			'minify_concatenate_css' => 0,
			'remove_unused_css'      => 1,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnSameWhenCombineCSSEnabled' => [
		'config' => [
			'value'     => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 1,
				'remove_unused_css'      => 0,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 0,
				'remove_unused_css'      => 0,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_css'             => 1,
			'minify_concatenate_css' => 1,
			'remove_unused_css'      => 0,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnUpdatedWhenCombineCssAndRUCSSEnabled' => [
		'config' => [
			'value'     => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 1,
				'remove_unused_css'      => 1,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 0,
				'remove_unused_css'      => 0,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_css'             => 1,
			'minify_concatenate_css' => 0,
			'remove_unused_css'      => 1,
			'cdn'                   => 0,
		],
	],
	'testShouldReturnUpdatedWhenCombineCssAndRUCSSEnabledBefore' => [
		'config' => [
			'value'     => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 1,
				'remove_unused_css'      => 1,
				'cdn'                   => 0,
			],
			'old_value' => [
				'minify_css'             => 1,
				'minify_concatenate_css' => 1,
				'remove_unused_css'      => 1,
				'cdn'                   => 0,
			],
		],
		'expected' => [
			'minify_css'             => 1,
			'minify_concatenate_css' => 0,
			'remove_unused_css'      => 1,
			'cdn'                   => 0,
		],
	],
];
