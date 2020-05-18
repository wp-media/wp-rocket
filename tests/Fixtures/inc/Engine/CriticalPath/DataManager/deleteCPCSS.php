<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'posts' => [
							'post-10.css' => 'test',
						],
					],
				],
			],
		],
	],

	'test_data' => [

		'testShouldSuccessfullyDeleteFile' => [
			'config'   => [
				'path'         => 'posts/post-10.css',
				'file_deleted' => true,
			],
			'expected' => [
				'deleted' => true,
			],
		],

		'testShouldBailOutFileNotExists' => [
			'config'   => [
				'path'         => 'posts/post-20.css',
				'file_deleted' => false,
			],
			'expected' => [
				'deleted' => false,
				'code'    => 'cpcss_not_exists',
				'message' => 'Critical CSS file does not exist',
				'data'    => [
					'status' => 400,
				],
			],
		],

		'testShouldBailOutFileExistsNotDeleted' => [
			'config'   => [
				'change_permissions' => true,
				'path'               => 'posts/post-10.css',
				'file_deleted'       => false,
			],
			'expected' => [
				'deleted' => false,
				'code'    => 'cpcss_deleted_failed',
				'message' => 'Critical CSS file cannot be deleted',
				'data'    => [
					'status' => 400,
				],
			],
		],
	],
];
