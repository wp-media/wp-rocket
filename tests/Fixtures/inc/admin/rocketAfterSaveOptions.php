<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                                => [
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
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
			],
		],
		'.htaccess'  => "Some contents.\n# BEGIN WP Rocket\nSome rules.\n# END WP Rocket\n",
	],
];
