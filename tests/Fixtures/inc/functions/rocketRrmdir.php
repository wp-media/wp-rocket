<?php

$i18n_plugins = require WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/pluginsData.php';

return [
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldDeleteOnlyASingleFile' => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/index.html',
			'to_preserve' => [],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/index.html' => null,
				],
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 0,
			],
		],

		'shouldDeleteHiddenFiles'                                        => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/hidden-files/',
			'to_preserve' => [],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/' => null,
				],
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
			],
		],

		// Should delete the directory and all of its entries.
		'example.org/lorem-ipsum_shouldDeleteSingleDir'                  => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/lorem-ipsum/',
			'to_preserve' => [],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/' => null,
				],
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
			],
		],
		'example.org/nec-ullamcorper_shouldDeleteSingleDirWithChildDirs' => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/nec-ullamcorper/',
			'to_preserve' => [],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/' => null,
				],
				'before_rocket_rrmdir' => 2,
				'after_rocket_rrmdir'  => 2,
			],
		],
		'example.org-wpmedia-123456_shouldDeleteAll'                     => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/',
			'to_preserve' => [],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
				],
				'before_rocket_rrmdir' => 4,
				'after_rocket_rrmdir'  => 4,
			],
		],
		'example.org_shouldDeleteAll'                                    => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org/',
			'to_preserve' => [],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/' => null,
				],
				'before_rocket_rrmdir' => 10,
				'after_rocket_rrmdir'  => 10,
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
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/' => null,
				],
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
			],
		],

		'example.org_shouldBailOutWhenRootDirIsPreserved_de' => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org/de',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/de/' => null,
				],
				'before_rocket_rrmdir' => 1,
				'after_rocket_rrmdir'  => 1,
			],
		],
		// Delete all except the de directory/files.
		'example.org_shouldDeleteAllExceptDEentries'         => [
			'to_delete'   => 'wp-content/cache/wp-rocket/example.org',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
			],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'             => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'        => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/'                  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/'              => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/'                    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'          => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'           => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'       => null,
				],
				'before_rocket_rrmdir' => 9,
				'after_rocket_rrmdir'  => 9,
			],
		],
		'example.org_shouldDeleteAllExceptFEandDE'           => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'       => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/'            => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/'        => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'     => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/' => null,
				],
				'before_rocket_rrmdir' => 8,
				'after_rocket_rrmdir'  => 8,
			],
		],
		// Should delete all in the example.org directory _when_ the preserved language does not exist in the cache.
		'example.org_shouldDeleteAllWhenLangNotPreserved'    => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org/',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/nl', // doesn't have files in the cache.
			],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/' => null,
				],
				'before_rocket_rrmdir' => 10,
				'after_rocket_rrmdir'  => 10,
			],
		],

		'example.org-wpmedia-123456_shouldDeleteAllExceptFEandDE' => [
			'to_delete'   => 'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => null,
				],
				'before_rocket_rrmdir' => 2,
				'after_rocket_rrmdir'  => 2,
			],
		],

		'shouldDeleteAllWhenLangNotPreserved' => [
			'to_delete'   => 'vfs://public/wp-content/cache/',
			'to_preserve' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/de',
				'vfs://public/wp-content/cache/wp-rocket/example.org(.*)/fr',
			],
			'expected'    => [
				'removed'              => [
					'vfs://public/wp-content/cache/min/'                          => null,
					'vfs://public/wp-content/cache/busting/'                      => null,
					'vfs://public/wp-content/cache/critical-css/index.php'        => null,
					'vfs://public/wp-content/cache/critical-css/1/home.css'       => null,
					'vfs://public/wp-content/cache/critical-css/1/front_page.css' => null,
					'vfs://public/wp-content/cache/critical-css/1/category.css'   => null,
					'vfs://public/wp-content/cache/critical-css/1/post_tag.css'   => null,
					'vfs://public/wp-content/cache/critical-css/1/page.css'       => null,
					'vfs://public/wp-content/cache/critical-css/1/posts/'         => [],
					'vfs://public/wp-content/cache/critical-css/2/home.css'       => null,
					'vfs://public/wp-content/cache/critical-css/2/front_page.css' => null,
					'vfs://public/wp-content/cache/critical-css/2/category.css'   => null,
					'vfs://public/wp-content/cache/critical-css/2/post_tag.css'   => null,
					'vfs://public/wp-content/cache/critical-css/2/page.css'       => null,
					'vfs://public/wp-content/cache/critical-css/2/posts/'         => [],
					'vfs://public/wp-content/cache/wp-rocket/index.html'          => null,

					'vfs://public/wp-content/cache/wp-rocket/baz.example.org/' => [],

					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'             => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'        => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index-mobile.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/blog/'                  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/category/'              => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hidden-files/'          => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/lorem-ipsum/'           => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/nec-ullamcorper/'       => null,

					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/index.html_gzip' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/lorem-ipsum/'    => null,

					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html'       => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/index.html_gzip'  => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/nec-ullamcorper/' => null,
				],
				'before_rocket_rrmdir' => 37,
				'after_rocket_rrmdir'  => 37,
			],
		],
	],
];
