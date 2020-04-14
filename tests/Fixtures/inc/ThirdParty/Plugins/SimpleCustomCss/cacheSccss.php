<?php


return [
	'vfs_dir'   => 'wp-content/cache/busting/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'busting' => [
					'index.php' => '<?php',
					'1'         => [
						'.'         => '',
						'..'        => '',
						'sccss.css' => '.simple-custom-css { color: red; }',
					],
					'2'         => [
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
			// get_current_blog_id()
			2,
			// Path
			'wp-content/cache/busting/2/sccss.css',
		],
		'testShouldCreateTheFileAndBustingFolder' => [
			// get_current_blog_id()
			3,
			// Path
			'wp-content/cache/busting/3/sccss.css',
		],
	],
];
