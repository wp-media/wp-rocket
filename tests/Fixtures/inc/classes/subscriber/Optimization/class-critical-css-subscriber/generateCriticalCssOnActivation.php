<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'            => '',
						'..'           => '',
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					],
					'2' => [
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'non_multisite' => [
			'testShouldBailOutWhenCriticalCSSOptionIsFalse'  => [
				'values'         => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 0 ],
				],
				'shouldGenerate' => false,
			],
			'testShouldBailOutWhenCriticalCssPathIsNotEmpty' => [
				'values'         => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'shouldGenerate' => false,
			],
		],

		'multisite' => [
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 0 ],
				],
				'blog_id'         => 2,
				'should_generate' => false,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'blog_id'         => 2,
				'should_generate' => false,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'site_id'         => 3,
				'should_generate' => true,
			],
		],
	],
];
