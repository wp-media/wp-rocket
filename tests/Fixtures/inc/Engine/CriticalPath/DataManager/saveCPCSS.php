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
		'testShouldUpdateExistingCPCSSFile' => [
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/post-1.css',
			'cpcss_code' => 'body{color:red;}',
			'is_mobile'  => false,
			'expected'   => true,
		],
		'testShouldCreateCPCSSFileForPost500' => [
			'url'        => 'http://www.example.com/?p=500',
			'path'       => 'posts/post-500.css',
			'cpcss_code' => 'body{ color:red }',
			'is_mobile'  => false,
			'expected'   => true,
		],
		'testShouldCreateCPCSSFileForPostLoremIpsum' => [
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/lorem-ipsum.css',
			'cpcss_code' => 'body{ color:red; font-size: 2em } h1 { color: black }',
			'is_mobile'  => false,
			'expected'   => true,
		],

		'testShouldUpdateExistingCPCSSFileWhenMobile' => [
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/post-1-mobile.css',
			'cpcss_code' => 'body{color:red;}',
			'is_mobile'  => true,
			'expected'   => true,
		],
		'testShouldCrateCPCSSFileForPost500WhenMobile' => [
			'url'        => 'http://www.example.com/?p=500',
			'path'       => 'posts/post-500-mobile.css',
			'cpcss_code' => 'body{ color:red }',
			'is_mobile'  => true,
			'expected'   => true,
		],
		'testShouldCrateCPCSSFileForPostLoremIpsumWhenMobile' => [
			'url'        => 'http://www.example.com/?p=1',
			'path'       => 'posts/lorem-ipsum-mobile.css',
			'cpcss_code' => 'body{ color:red; font-size: 2em } h1 { color: black }',
			'is_mobile'  => true,
			'expected'   => true,
		],
	],
];
