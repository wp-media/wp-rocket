<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldPersistNothingState'       => [
			'config'   => [
				'state' => 'nothing',
			],
			'expected' => [
				'cdn_state' => 'nothing',
			],
		],
		'shouldPersistByocdnState'        => [
			'config'   => [
				'state' => 'byocdn',
			],
			'expected' => [
				'cdn_state' => 'byocdn',
			],
		],
		'shouldPersistRocketcdnFreeState' => [
			'config'   => [
				'state' => 'rocketcdn_free',
			],
			'expected' => [
				'cdn_state' => 'rocketcdn_free',
			],
		],
		'shouldPersistRocketcdnPaidState' => [
			'config'   => [
				'state' => 'rocketcdn_paid',
			],
			'expected' => [
				'cdn_state' => 'rocketcdn_paid',
			],
		],
	],
];
