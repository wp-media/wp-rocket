<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	'test_data' => [
		'non_multisite' => [
			'testShouldSuccessfullySaveFile'     => [
				'config'   => [
					'path'  => 'post-10.css',
					'cpcss_code'  => 'body{color:red;}',
				],
				'expected' => [
					'saved' => true
				]
			],
			'testShouldBailOutOnFileCreation'     => [
				'config'   => [
					'path'  => 'post-1000.css',
					'cpcss_code'  => 'body{color:red;}',
				],
				'expected' => [
					'saved' => false
				]
			],
		],
		'multisite' => []
	],
];
