<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	'test_data' => [
		'non_multisite' => [
			'testShouldJobIdSavedIntoCache'     => [
				'config'   => [
					'item_url' => 'http://www.example.com/?p=1',
					'job_id'   => 1,
					'saved'    => true
				],
				'expected' => [
					'saved' => true,
				]
			],
			'testShouldBailOutOnSavingCache'     => [
				'config'   => [
					'item_url'      => 'http://www.example.com/?p=2',
					'job_id' => 5,
					'saved' => false
				],
				'expected' => [
					'saved' => false,
				]
			],
		],
		'multisite' => []
	],
];
