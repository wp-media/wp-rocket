<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'posts' => [
							'post-1.css' => 'body{color:blue;}',
						],
					],
				],
			],
		],
	],

	'test_data' => [

		// Should update an existing file.
		[
			'path'       => 'posts/post-1.css',
			'cpcss_code' => 'body{color:red;}',
			'expected'   => true,
		],

		// Should create non-existent files.
		[
			'path'       => 'posts/post-500.css',
			'cpcss_code' => 'body{ color:red }',
			'expected'   => true,
		],

		[
			'path'       => 'lorem-ipsum.css',
			'cpcss_code' => 'body{ color:red; font-size: 2em } h1 { color: black }',
			'expected'   => true,
		],
	],
];
