<?php


return [
	'vfs_dir'   => 'wp-content/cache/busting/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'busting' => [
					'1' => [
						'.'         => '',
						'..'        => '',
						'sccss.css' => '.simple-custom-css { color: red; }',
					],
					'2' => [
						'.'         => '',
						'..'        => '',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'testShouldCreateTheFile' => [
			// Busting Folder Path
			'wp-content/cache/busting/2/',
			// Path
			'wp-content/cache/busting/2/sccss.css',
			// URL
			'http://example.org/wp-content/cache/busting/2/sccss.css',
		],
		'testShouldCreateTheFileAndBustingFolder' => [
			// Busting Folder Path
			'wp-content/cache/busting/3/',
			// Path
			'wp-content/cache/busting/3/sccss.css',
			// URL
			'http://example.org/wp-content/cache/busting/3/sccss.css',
		],
	],
];
