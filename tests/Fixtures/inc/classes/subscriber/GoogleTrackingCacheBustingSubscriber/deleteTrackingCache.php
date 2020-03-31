<?php


return [
	'vfs_dir'   => 'wp-content/cache/busting/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'busting' => [
					'google-tracking'   => [
						'ga-e8ea7a8d1e93e8764a84a0f3df4644de.js',
						'ga-b673caf846c3692bfbbcb8705de75f8a.js',
						'gtm-c8cfdb3d8498d79371bc5fdb265e7e0b.js',
						'gtm-9e69e41568c60c2c1f9a7f29226a2eb4.js',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [],
];
