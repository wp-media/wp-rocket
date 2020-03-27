<?php

return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/example.org/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                             => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'about'           => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
						'category'        => [
							'wordpress' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
						],
						'blog'            => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'en'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		[
			[
				'title' => 'Duis aute irure',
				'text'  => '',
			],
		],
		[
			[
				'title' => '',
				'text'  => 'Ut enim ad minim veniam',
			],
		],
		[
			[
				'title' => 'Lorem ipsum',
				'text'  => 'Ut enim ad minim veniam',
			],
		],
	],
];
