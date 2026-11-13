<?php

/*
 * `lock` is null, 'fresh' (held by another worker) or 'stale' (abandoned). `column_first` adds the
 * url_hash column before the run, and `duplicate_hashes` then seeds two rows sharing one hash.
 * `null_hash_rows` is only checked when it is not null.
 */
return [
	'test_data' => [
		'shouldAddColumnAndKeyAndKeepLegacyRows' => [
			'config'   => [
				'legacy_rows'      => true,
				'column_first'     => false,
				'duplicate_hashes' => false,
				'lock'             => null,
				'throttled'        => false,
				'runs'             => 1,
			],
			'expected' => [
				'results'        => [ true ],
				'column'         => true,
				'index'          => true,
				'lock_row'       => false,
				'throttled'      => false,
				'null_hash_rows' => 2,
			],
		],
		'shouldBeIdempotent' => [
			'config'   => [
				'legacy_rows'      => false,
				'column_first'     => false,
				'duplicate_hashes' => false,
				'lock'             => null,
				'throttled'        => false,
				'runs'             => 2,
			],
			'expected' => [
				'results'        => [ true, true ],
				'column'         => true,
				'index'          => true,
				'lock_row'       => false,
				'throttled'      => false,
				'null_hash_rows' => 0,
			],
		],
		'shouldReturnFalseWithoutThrottleWhenLockIsHeld' => [
			'config'   => [
				'legacy_rows'      => false,
				'column_first'     => false,
				'duplicate_hashes' => false,
				'lock'             => 'fresh',
				'throttled'        => false,
				'runs'             => 1,
			],
			'expected' => [
				'results'        => [ false ],
				'column'         => false,
				'index'          => false,
				'lock_row'       => true,
				'throttled'      => false,
				'null_hash_rows' => null,
			],
		],
		'shouldReclaimStaleLock' => [
			'config'   => [
				'legacy_rows'      => false,
				'column_first'     => false,
				'duplicate_hashes' => false,
				'lock'             => 'stale',
				'throttled'        => false,
				'runs'             => 1,
			],
			'expected' => [
				'results'        => [ true ],
				'column'         => true,
				'index'          => true,
				'lock_row'       => false,
				'throttled'      => false,
				'null_hash_rows' => 0,
			],
		],
		'shouldNullDuplicateHashesAndRetryTheKey' => [
			'config'   => [
				'legacy_rows'      => false,
				'column_first'     => true,
				'duplicate_hashes' => true,
				'lock'             => null,
				'throttled'        => false,
				'runs'             => 1,
			],
			'expected' => [
				'results'        => [ true ],
				'column'         => true,
				'index'          => true,
				'lock_row'       => false,
				'throttled'      => false,
				'null_hash_rows' => 2,
			],
		],
		'shouldDoNothingWhileThrottled' => [
			'config'   => [
				'legacy_rows'      => false,
				'column_first'     => false,
				'duplicate_hashes' => false,
				'lock'             => null,
				'throttled'        => true,
				'runs'             => 1,
			],
			'expected' => [
				'results'        => [ false ],
				'column'         => false,
				'index'          => false,
				'lock_row'       => false,
				'throttled'      => true,
				'null_hash_rows' => null,
			],
		],
	],
];
