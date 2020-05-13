<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	'test_data' => [
		'non_multisite' => [
			'testShouldBailoutIfNotCachedBefore'     => [
				'config'   => [
					'item_url'      => 'http://www.example.com/?p=1',
					'job_id' => null
				],
				'expected' => [
					'job_id' => null,
					'applied_filters' => []
				]
			],
			'testShouldGetCachedJobIdIfCachedBefore'     => [
				'config'   => [
					'item_url'      => 'http://www.example.com/?p=2',
					'job_id' => 5
				],
				'expected' => [
					'job_id' => 5,
					'applied_filters' => [
						'transient_rocket_specific_cpcss_job_'.md5( 'http://www.example.com/?p=2' )
					]
				]
			],
		],
		'multisite' => []
	],
];
