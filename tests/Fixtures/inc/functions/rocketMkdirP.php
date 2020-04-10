<?php

return [
	'vfs_dir'   => 'wp-content/cache/min/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache'            => [
				'wp-rocket'    => [
					'example.org'                => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-123456' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
				'min'          => [
					'1' => [
						'123456.css' => '',
						'123456.js'  => '',
					],
				],
				'busting'      => [
					'1' => [
						'ga-123456.js' => '',
					],
				],
				'critical-css' => [
					'1' => [
						'front-page.php' => '',
						'blog.php'       => '',
					],
				],
			],
			'wp-rocket-config' => [
				'example.org.php' => 'test',
			],
		],
	],

	// Test data.
	'test_data' => [
		[
			'target'       => 'wp-content/cache/min/1/',
			'should_mkdir' => false,
		],
		[
			'target'       => 'wp-content/cache/wp-rocket/example.org/about/',
			'should_mkdir' => true,
		],
		[
			'target'       => 'wp-content/cache/wp-rocket/example.org/parent1/child1/grandchild1',
			'should_mkdir' => true,
		],
		[
			'target'       => '/',
			'should_mkdir' => false,
		],
		[
			'target'       => '/test1',
			'should_mkdir' => true,
		],

		// Double //.
		[
			'target'       => 'wp-content//cache//wp-rocket//example.org/parent1//child1//grandchild1//greatgrandchild1',
			'should_mkdir' => true,
			'new_path'     => 'vfs://public/wp-content/cache/wp-rocket/example.org/parent1/child1/grandchild1/greatgrandchild1/',
		],

		// Try with non-stream URLs.
		[
			'target'       => 'bitcoin://example.org/2020/03/',
			'should_mkdir' => true,
			'new_path'     => 'vfs://public/bitcoin:/example.org/2020/03/',
		],
		[
			'target'       => 'content://example.org/wp-content/cache/',
			'should_mkdir' => true,
			'new_path'     => 'vfs://public/content:/example.org/wp-content/cache/',
		],
	],
];
