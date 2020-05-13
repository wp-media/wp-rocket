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
					'applied_filters' => [
						'set_transient_rocket_specific_cpcss_job_'.md5( 'http://www.example.com/?p=1' )
					]
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
					'applied_filters' => []
				]
			],
		],
		'multisite' => []
	],
];
