<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache'            => [
				'wp-rocket'    => [
					'example.org'                 => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'de'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'fr'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'hidden-files'    => [
							'.mobile-active' => '',
							'.no-webp'       => '',
						],
						'lorem-ipsum'     => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'nec-ullamcorper' => [
							'enim-nunc-faucibus' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
							'index.html'         => '',
							'index.html_gzip'    => '',
						],
					],
					'example.org-wpmedia-123456'  => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'lorem-ipsum'     => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
					'example.org-wpmedia1-123456' => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'nec-ullamcorper' => [
							'enim-nunc-faucibus' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
							'index.html'         => '',
							'index.html_gzip'    => '',
						],
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
		'shouldHandleSingleFile'  => [
			'to_delete'   => 'example.org/lorem-ipsum/index.html',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 0,
			],
		],
		'shouldDeleteHiddenFiles' => [
			'to_delete'   => 'example.org/hidden-files/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
			],
		],
//		'shouldBailOutWhenDirectoryShouldBePreserved' => [
//			'to_delete'   => 'example.org/fr',
//			'to_preserve' => [
//				'vfs://public/wp-content/cache/wp-rocket/example.org/de',
//				'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
//			],
//			'expected'    => [
//				'before_rocket_rrmdir' => 0,
//				'after_rocket_rrmdir'  => 0,
//			],
//		],

		// Should delete the directory and all of its entries.
		'shouldDeleteSingleDir'   => [
			'to_delete'   => 'example.org/lorem-ipsum/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
			],
		],
		[
			'to_delete'   => 'example.org/nec-ullamcorper/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 2,
				'after_rocket_rrmdir'  => 2,
			],
		],
		[
			'to_delete'   => 'example.org-wpmedia1-123456',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 3,
				'after_rocket_rrmdir'  => 3,
			],
		],
	],
];
