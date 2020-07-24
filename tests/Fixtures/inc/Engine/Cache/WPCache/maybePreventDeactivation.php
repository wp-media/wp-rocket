<?php

return [
	'vfs_dir' => 'public/',

	'test_data' => [

		'testShouldBailOutWhenConfigFileFound' => [
			'config'   => [
				'file_exist' => true,
			],
			'expected' => [],
		],

		'testShouldBailOutWhenSetCacheConstFilterFalse' => [
			'config'   => [
				'file_exist' => false,
				'set_filter_to_false'   => true,
			],
			'expected' => [],
		],

		'testShouldAddCauseToCausesWhenPrevented' => [
			'config' => [
				'file_exist' => false,
				'set_filter_to_false' => false,
			],
			'expected'   => [ 'wpconfig' ],
		],
	]
];
