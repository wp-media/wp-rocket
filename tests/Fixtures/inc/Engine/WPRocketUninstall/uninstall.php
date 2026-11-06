<?php

return [
	'vfs_dir'   => 'wp-content/',

	// Virtual file system structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-123456' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
				'min' => [
					'1' => [
						'123456.css' => '',
						'123456.js'  => '',
					],
				],
				'busting' => [
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
			]
		],
	],
];
