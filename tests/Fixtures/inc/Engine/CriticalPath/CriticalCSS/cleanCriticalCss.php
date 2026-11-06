<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'                     => '',
						'..'                    => '',
						'folder'                => [
							'.'        => '',
							'..'       => '',
							'file.css' => '.p { color: red; }',
						],
						'home.css'              => '.p { color: red; }',
						'home-mobile.css'       => '.p { color: blue; }',
						'front_page.css'        => '.p { color: red; }',
						'front_page-mobile.css' => '.p { color: blue; }',
						'category.css'          => '.p { color: red; }',
						'category-mobile.css'   => '.p { color: blue; }',
						'post_tag.css'          => '.p { color: red; }',
						'post_tag-mobile.css'   => '.p { color: blue; }',
						'page.css'              => '.p { color: red; }',
						'page-mobile.css'       => '.p { color: blue; }',
					],
				],
			],
		],
	],

	// Test Data
	'test_data' => [
		'testShouldDeleteFilesFromRootFolderButKeepChildFoldersWhenAll' => [
			'config'   => [
				'blog_id' => 1,
				'version' => 'all',
			],
			'delete'   => [
				'home.css',
				'front_page.css',
				'category.css',
				'post_tag.css',
				'page.css',
				'home-mobile.css',
				'front_page-mobile.css',
				'category-mobile.css',
				'post_tag-mobile.css',
				'page-mobile.css',
			],
			'preserve' => [
				'folder',
				'folder/file.css',
			],
		],

		'testShouldDeleteDefaultFilesFromRootFolderButKeepChildFoldersWhenDefault' => [
			'config'   => [
				'blog_id' => 1,
				'version' => 'default',
			],
			'delete'   => [
				'home.css',
				'front_page.css',
				'category.css',
				'post_tag.css',
				'page.css',
			],
			'preserve' => [
				'folder',
				'folder/file.css',
				'home-mobile.css',
				'front_page-mobile.css',
				'category-mobile.css',
				'post_tag-mobile.css',
				'page-mobile.css',
			],
		],

		'testShouldDeleteMobileFilesFromRootFolderButKeepChildFoldersWhenMobile' => [
			'config'   => [
				'blog_id' => 1,
				'version' => 'mobile',
			],
			'delete'   => [
				'home-mobile.css',
				'front_page-mobile.css',
				'category-mobile.css',
				'post_tag-mobile.css',
				'page-mobile.css',
			],
			'preserve' => [
				'folder',
				'folder/file.css',
				'home.css',
				'front_page.css',
				'category.css',
				'post_tag.css',
				'page.css',
			],
		],
	],
];
