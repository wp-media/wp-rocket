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
							'post-1.css'        => 'body{color:blue;}',
							'post-1-mobile.css' => 'body{color:blue;}',
						],
					],
				],
			],
		],
	],

	'test_data' => [
		// Should update an existing file.
		[
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/post-1.css',
			'cpcss_code' => 'body{color:red;}',
			'is_mobile'  => false,
			'expected'   => true,
		],
		// Should create non-existent files.
		[
			'url'        => 'http://www.example.com/?p=500',
			'path'       => 'posts/post-500.css',
			'cpcss_code' => 'body{ color:red }',
			'is_mobile'  => false,
			'expected'   => true,
		],
		[
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/lorem-ipsum.css',
			'cpcss_code' => 'body{ color:red; font-size: 2em } h1 { color: black }',
			'is_mobile'  => false,
			'expected'   => true,
		],

		// Mobile CPCSS
		// Should update an existing file.
		[
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/post-1-mobile.css',
			'cpcss_code' => 'body{color:red;}',
			'is_mobile'  => true,
			'expected'   => true,
		],
		// Mobile CPCSS
		// Should create non-existent files.
		[
			'url'        => 'http://www.example.com/?p=500',
			'path'       => 'posts/post-500-mobile.css',
			'cpcss_code' => 'body{ color:red }',
			'is_mobile'  => true,
			'expected'   => true,
		],
		[
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/lorem-ipsum-mobile.css',
			'cpcss_code' => 'body{ color:red; font-size: 2em } h1 { color: black }',
			'is_mobile'  => true,
			'expected'   => true,
		],
	],
];
