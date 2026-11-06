<?php

$locked_row = [
	'url' => 'https://example.org/url',
	'status' => 'completed',
	'is_mobile' => false,
	'is_locked' => true,
];

return [
	'lockedRowShouldBePendingAndUnlocked' => [
		'config' => [
			'manual_preload' => true,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'https://example.org/url',
			'data' => [ $locked_row ],
		],
		'expected' => [
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
					'is_mobile' => false,
					'is_locked' => false,
				],
			],
			'missing' => [
				[
					'url' => 'https://example.org/url',
					'is_locked' => true,
				],
			],
		],
	],
	'unlockedRowShouldStayUnlocked' => [
		'config' => [
			'manual_preload' => true,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'https://example.org/url',
			'data' => [ array_merge( $locked_row, [ 'is_locked' => false ] ) ],
		],
		'expected' => [
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
					'is_mobile' => false,
					'is_locked' => false,
				],
			],
			'missing' => [],
		],
	],
	'manualPreloadOffShouldLeaveRowUntouched' => [
		'config' => [
			'manual_preload' => false,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'https://example.org/url',
			'data' => [ $locked_row ],
		],
		'expected' => [
			'data' => [ $locked_row ],
			'missing' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
				],
			],
		],
	],
	'excludedUrlShouldBeDeleted' => [
		'config' => [
			'manual_preload' => true,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'https://example.org/page/2/',
			'data' => [ array_merge( $locked_row, [ 'url' => 'https://example.org/page/2/' ] ) ],
		],
		'expected' => [
			'data' => [],
			'missing' => [
				[
					'url' => 'https://example.org/page/2/',
				],
			],
		],
	],
	'wildcardUrlShouldBeStrippedThenUnlocked' => [
		'config' => [
			'manual_preload' => true,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'https://example.org/url/*',
			'data' => [ $locked_row ],
		],
		'expected' => [
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
					'is_mobile' => false,
					'is_locked' => false,
				],
			],
			'missing' => [
				[
					'url' => 'https://example.org/url',
					'is_locked' => true,
				],
			],
		],
	],
];
