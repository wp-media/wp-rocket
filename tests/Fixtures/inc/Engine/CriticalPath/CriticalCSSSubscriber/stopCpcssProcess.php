<?php

return [
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
			],
		],
	],
	'test_data' => [
		'testCPCSShouldNotStopProcessUpgrade' => [
			'config'   => [
				'old' => [ 'async_css' => 0 ],
				'new' => [ 'async_css' => 0 ],
				'upgrade_rollback' => 'upgrade',
			],
			'expected' =>
				false
			,
		],
		'testCPCSShouldStopProcessUpgrade' => [
			'config'   => [
				'old' => [ 'async_css' => 0 ],
				'new' => [ 'async_css' => 1 ],
				'upgrade_rollback' => 'upgrade',
			],
			'expected' =>
				true
			,
		],
		'testCPCSShouldNotStopProcessRollBack' => [
			'config'   => [
				'old' => [ 'async_css' => 0 ],
				'new' => [ 'async_css' => 0 ],
				'upgrade_rollback' => 'rollback',
			],
			'expected' =>
				false
			,
		],
		'testCPCSShouldStopProcessRollBack' => [
			'config'   => [
				'old' => [ 'async_css' => 0 ],
				'new' => [ 'async_css' => 1 ],
				'upgrade_rollback' => 'rollback',
			],
			'expected' =>
				true
			,
		],
	],
];
