<?php


return [
	'vfs_dir'   => 'wp-content/cache/busting/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'busting' => [
					'facebook-tracking' => [
						'fbsdk-en.js'            => '',
						'fbsdk-fr.js'            => '',
						'fbsdk-it.js'            => '',
						'fbpix-events-en-2.0.js' => '',
						'fbpix-events-fr-1.0.js' => '',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [],
];
