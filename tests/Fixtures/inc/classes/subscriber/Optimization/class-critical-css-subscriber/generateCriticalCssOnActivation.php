<?php

return [
	'vfs_dir'   => 'cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'cache' => [
			'critical-css' => [
				'1' => [
					'.'            => '',
					'..'           => '',
					'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		[
			// Old Value.
			[],
			// New Value.
			[],
		],
	],
];
