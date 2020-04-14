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
		'testShouldDeleteTheFileAndRecreateIt' => [
			// get_current_blog_id()
			1,
			// Path
			'wp-content/cache/busting/1/sccss.css',
		],
		'testShouldCreateTheFile' => [
			// get_current_blog_id()
			2,
			// Path
			'wp-content/cache/busting/2/sccss.css',
		],
		'testShouldCreateBustingFolderAndFile' => [
			// get_current_blog_id()
			3,
			// Path
			'wp-content/cache/busting/3/sccss.css',
		],
	],
];
