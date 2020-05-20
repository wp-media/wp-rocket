<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'              => '',
						'..'             => '',
						'folder'         => [
							'.'        => '',
							'..'       => '',
							'file.css' => '.p { color: red; }',
						],
						'home.css'       => '.p { color: red; }',
						'front_page.css' => '.p { color: red; }',
						'category.css'   => '.p { color: red; }',
						'post_tag.css'   => '.p { color: red; }',
						'page.css'       => '.p { color: red; }',
					],
					'2' => [
						'.'  => '',
						'..' => '',
					],
				],
			],
		],
	],
	// Test Data
	'test_data' => [
		'testShouldNotDeleteAnything'                            => [
			// Blog id
			2,
			// Deleted Files
			[],
			// Available Folders
			[],
		],
		'testShouldDeleteFilesFromRootFolderButKeepChildFolders' => [
			// Blog id
			1,
			// Deleted Files
			[
				'home.css',
				'front_page.css',
				'category.css',
				'post_tag.css',
				'page.css',
			],
			// Available Folders
			[
				'folder',
				'folder/file.css',
			],
		],
	],
];
