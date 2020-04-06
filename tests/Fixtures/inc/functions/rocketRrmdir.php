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
		'shouldHandleSingleFile'                        => [
			'to_delete'   => 'example.org/lorem-ipsum/index.html',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 0,
				'deleted'              => [
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html',
				],
			],
		],
		'shouldDeleteHiddenFiles'                       => [
			'to_delete'   => 'example.org/hidden-files/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
				'deleted'              => [
					'wp-content/cache/wp-rocket/example.org/hidden-files',
					'wp-content/cache/wp-rocket/example.org/hidden-files/.mobile-active',
					'wp-content/cache/wp-rocket/example.org/hidden-files/.no-webp',
				],
			],
		],

		// Should delete the directory and all of its entries.
		'shouldDeleteSingleDir'                         => [
			'to_delete'   => 'example.org/lorem-ipsum/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
				'deleted'              => [
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum',
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html',
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html_gzip',
				],
			],
		],
		[
			'to_delete'   => 'example.org/nec-ullamcorper/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 2,
				'after_rocket_rrmdir'  => 2,
				'deleted'              => [
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html_gzip',
				],
			],
		],
		'shouldDeleteAllOf_example.org-wpmedia1-123456' => [
			'to_delete'   => 'example.org-wpmedia1-123456',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 3,
				'after_rocket_rrmdir'  => 3,
				'deleted'              => [
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/index.html',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/nec-ullamcorper',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/nec-ullamcorper/enim-nunc-faucibus',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/nec-ullamcorper/enim-nunc-faucibus/index.html',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/nec-ullamcorper/index.html',
					'wp-content/cache/wp-rocket/example.org-wpmedia1-123456/nec-ullamcorper/index.html_gzip',
				],
			],
		],
		'shouldDeleteAllOf_example.org'                 => [
			'to_delete'   => 'example.org/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 7,
				'after_rocket_rrmdir'  => 7,
				'deleted'              => [
					'wp-content/cache/wp-rocket/example.org',
					'wp-content/cache/wp-rocket/example.org/index.html',
					'wp-content/cache/wp-rocket/example.org/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org/de',
					'wp-content/cache/wp-rocket/example.org/de/index.html',
					'wp-content/cache/wp-rocket/example.org/de/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org/fr',
					'wp-content/cache/wp-rocket/example.org/fr/index.html',
					'wp-content/cache/wp-rocket/example.org/fr/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org/hidden-files',
					'wp-content/cache/wp-rocket/example.org/hidden-files/.mobile-active',
					'wp-content/cache/wp-rocket/example.org/hidden-files/.no-webp',
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum',
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html',
					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html',
					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html_gzip',
				],
			],
		],

		// Should not delete directories or its entries when marked as preserved.
		'shouldBailOutWhenDirectoryShouldBePreserved' => [
			'to_delete'   => 'example.org/fr',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 0,
				'after_rocket_rrmdir'  => 0,
				'deleted' => []
			],
		],
//		'shouldBailOutWhenDirectoryShouldBePreserved' => [
//			'to_delete'   => 'example.org',
//			'to_preserve' => [
//				'vfs://public/wp-content/cache/wp-rocket/example.org/de',
//				'vfs://public/wp-content/cache/wp-rocket/example.org/fr',
//			],
//			'expected'    => [
//				'before_rocket_rrmdir' => 0,
//				'after_rocket_rrmdir'  => 0,
//				'deleted' => [
//					'wp-content/cache/wp-rocket/example.org/index.html',
//					'wp-content/cache/wp-rocket/example.org/index.html_gzip',
//					'wp-content/cache/wp-rocket/example.org/hidden-files',
//					'wp-content/cache/wp-rocket/example.org/hidden-files/.mobile-active',
//					'wp-content/cache/wp-rocket/example.org/hidden-files/.no-webp',
//					'wp-content/cache/wp-rocket/example.org/lorem-ipsum',
//					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html',
//					'wp-content/cache/wp-rocket/example.org/lorem-ipsum/index.html_gzip',
//					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper',
//					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus',
//					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html',
//					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/enim-nunc-faucibus/index.html_gzip',
//					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html',
//					'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/index.html_gzip',
//				]
//			],
//		],
	],
];
