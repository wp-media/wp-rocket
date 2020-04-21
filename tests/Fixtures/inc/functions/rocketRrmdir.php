<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php',

	// Test data.
	'test_data' => [
		'shouldDeleteOnlyASingleFile'                                    => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/index.html',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 0,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/index.html' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html_gzip'             => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],
		'shouldDeleteHiddenFiles'                                        => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/hidden-files/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                   => true,
					'vfs://public/wp-content/cache/busting/'                               => true,
					'vfs://public/wp-content/cache/critical-css/'                          => true,
					'vfs://public/wp-content/cache/wp-rocket/'                             => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                   => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html_gzip'              => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'       => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'     => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'   => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'            => true,
				],
			],
		],

		// Should delete the directory and all of its entries.
		'example.org/lorem-ipsum_shouldDeleteSingleDir'                  => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/lorem-ipsum/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                   => true,
					'vfs://public/wp-content/cache/busting/'                               => true,
					'vfs://public/wp-content/cache/critical-css/'                          => true,
					'vfs://public/wp-content/cache/wp-rocket/'                             => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                   => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'       => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'   => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'            => true,
				],
			],
		],
		'example.org/nec-ullamcorper_shouldDeleteSingleDirWithChildDirs' => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 2,
				'after_rocket_rrmdir'  => 2,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'      => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip' => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'   => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'    => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],
		'example.org-wpmedia-123456_shouldDeleteAll'                     => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 4,
				'after_rocket_rrmdir'  => 4,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia1-123456' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                 => true,
					'vfs://public/wp-content/cache/busting/'                             => true,
					'vfs://public/wp-content/cache/critical-css/'                        => true,
					'vfs://public/wp-content/cache/wp-rocket/'                           => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                 => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'               => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/' => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'          => true,
				],
			],
		],
		'example.org_shouldDeleteAll'                                    => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org/',
			'to_preserve' => [],
			'expected'    => [
				'before_rocket_rrmdir' => 7,
				'after_rocket_rrmdir'  => 7,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],

		/**
		 * Should not delete directories or its entries when marked as preserved.
		 */
		'example.org_shouldBailOutWhenRootDirIsPreserved_fr'             => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/fr',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
				'removed'              => [],
			],
		],
		'example.org_shouldBailOutWhenRootDirIsPreserved_de'             => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/de',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
				'removed'              => [],
			],
		],
		// Delete all except the de directory/files.
		'example.org_shouldDeleteAllExceptDEentries'                     => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 6,
				'after_rocket_rrmdir'  => 6,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr'              => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files'    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum'     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],
		'example.org_shouldDeleteAllExceptFEandDE'                       => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 5,
				'after_rocket_rrmdir'  => 5,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files'    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum'     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'             => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr'              => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],
		// Should delete all in the example.org directory _when_ the preserved language does not exist in the cache.
		'example.org_shouldDeleteAllWhenLangNotPreserved'                => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org/',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/nl', // doesn't have files in the cache.
			],
			'expected'    => [
				'before_rocket_rrmdir' => 7,
				'after_rocket_rrmdir'  => 7,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files'    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum'     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper' => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                  => true,
					'vfs://public/wp-content/cache/busting/'                              => true,
					'vfs://public/wp-content/cache/critical-css/'                         => true,
					'vfs://public/wp-content/cache/wp-rocket/'                            => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'           => true,
				],
			],
		],

		'example.org-wpmedia-123456_shouldDeleteAllExceptFEandDE' => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 2,
				'after_rocket_rrmdir'  => 2,
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum'     => null,
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                                     => true,
					'vfs://public/wp-content/cache/busting/'                                 => true,
					'vfs://public/wp-content/cache/critical-css/'                            => true,
					'vfs://public/wp-content/cache/wp-rocket/'                               => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                     => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                   => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'    => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'     => true,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/'              => true,
				],
			],
		],

		'shouldDeleteAllWhenLangNotPreserved' => [
			'to_delete'   => 'vfs://public/wp-content/cache/',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'before_rocket_rrmdir' => 19,
				'after_rocket_rrmdir'  => 19,
				'removed'              => [
					'vfs://public/wp-content/cache/min/'                                                 => null,
					'vfs://public/wp-content/cache/busting'                                              => null,
					'vfs://public/wp-content/cache/critical-css'                                         => null,
					'vfs://public/wp-content/cache/wp-rocket/index.html'                                 => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files'                   => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum'                    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum'     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper'  => null,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'                           => [],
				],
				'not_removed'          => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/wp-rocket/'                               => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/'                   => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/'    => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/de/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/fr/' => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'     => false,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/de/'  => true,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/fr/'  => true,
				],
			],
		],
	],
];
