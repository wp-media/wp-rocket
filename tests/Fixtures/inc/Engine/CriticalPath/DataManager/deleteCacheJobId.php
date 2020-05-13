<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	'test_data' => [
		'non_multisite' => [
			'testShouldJobIdDeletedFromCache'     => [
				'config'   => [
					'item_url' => 'http://www.example.com/?p=1',
					'deleted'  => true
				],
				'expected' => [
					'deleted'  => true,
				]
			],
			'testShouldBailOutOnSavingCache'     => [
				'config'   => [
					'item_url' => 'http://www.example.com/?p=2',
					'deleted'  => false
				],
				'expected' => [
					'deleted' => false,
				]
			],
		],
		'multisite' => []
	],
];
