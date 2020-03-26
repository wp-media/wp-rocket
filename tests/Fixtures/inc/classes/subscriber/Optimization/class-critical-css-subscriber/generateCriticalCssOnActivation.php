<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'2' => [
						'.'            => '',
						'..'           => '',
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'testShouldBailOutWhenCriticalCssPathIsNotEmpty'           => [
			[
				'path' => 'wp-content/cache/critical-css/2/',
			],
			// Old Value.
			[ 'async_css' => 0 ],
			// New Value.
			[ 'async_css' => 1 ],
		],
		'testShouldInvokeProcessHandlerWhenCriticalCssPathIsEmpty' => [
			[
				'path' => 'wp-content/cache/critical-css/1/',
			],
			// Old Value.
			[ 'async_css' => 0 ],
			// New Value.
			[ 'async_css' => 1 ],
		],
	],
];
