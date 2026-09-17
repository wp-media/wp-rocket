<?php

// Two well-formed 32-character lowercase hex hashes, matching the real
// `data-rocket-location-hash` format produced server-side (md5()).
$valid_hash_1 = 'db47c7d69edcf4565baa182deb470091';
$valid_hash_2 = 'db47c7d69edcf4565baa182deb470092';

return [
	'testShouldBailoutWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [ $valid_hash_1, $valid_hash_2 ],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_1, $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => false,
			'message' => 'not allowed',
		],
	],
	'testShouldBailoutWhenDBError' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [ $valid_hash_1, $valid_hash_2 ],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_1, $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => false,
			'message' => 'error when adding the entry to the database',
		],
	],
	'testShouldAddItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [ $valid_hash_1, $valid_hash_2 ],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_1, $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode( [ $valid_hash_1, $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenMobile' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'results' => json_encode(
				[
					'lrc' => [ $valid_hash_1, $valid_hash_2 ],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_1, $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode( [ $valid_hash_1, $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	// A value shaped like a hash but carrying an XSS payload no longer gets tag-stripped and
	// stored (as it did before this fix) - once stripped it no longer matches the strict
	// 32-hex-char shape, so it's rejected outright. Only the well-formed hash in the same
	// payload is persisted (partial acceptance, not all-or-nothing).
	'testShouldSanitizeBelowTheFold' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'results' => json_encode(
				[
					'lrc' => [
						'db47c7d69edcf4565<script>alert("Test XSS");</script>baa182deb470091',
						$valid_hash_2,
					],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode( [ $valid_hash_2 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	// Root-cause coverage for the vulnerability: a regex-metacharacter payload (the exact shape
	// of the DoS reproduction) must be rejected/skipped, never persisted - even when submitted
	// alongside a legitimate hash in the same request.
	'testShouldRejectMaliciousRegexPayload' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [ '(?s:.*)', $valid_hash_1 ],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_1 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode( [ $valid_hash_1 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	// A non-string element (e.g. a nested array/object from a crafted `results.lrc` JSON payload)
	// must be skipped via the is_string() guard without emitting a PHP warning from preg_match().
	'testShouldSkipNonStringElement' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [ [ 'nested', 'array' ], $valid_hash_1 ],
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [ $valid_hash_1 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode( [ $valid_hash_1 ] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldNotAddItemToDBWhenNoData' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.com',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenScriptTimeout' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.com',
			'is_mobile' => false,
			'status'    => 'timeout',
			'results' => json_encode(
				[
					'lrc' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'failed',
				'error_message'  => 'Script timeout',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'failed',
				'error_message'  => 'Script timeout',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
];
