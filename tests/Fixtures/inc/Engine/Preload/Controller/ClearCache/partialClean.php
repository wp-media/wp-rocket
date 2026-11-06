<?php
return [
	'testAddUrls' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => false,
			'is_excluded_by_filter' => false,
		],
		'expected' => [
			'urls' => [
				[
					[
						'url' => 'url',
						'status' => 'pending',
					]
				],
				[
					[
						'url' => 'url1',
						'status' => 'pending',
					]
				]
			]
		]
	],
	'excludedShouldNotAdd' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => true,
			'is_excluded_by_filter' => false,
		],
		'expected' => [
			'urls' => [
				[
					[
						'url' => 'url',
						'status' => 'pending',
					]
				],
				[
					[
						'url' => 'url1',
						'status' => 'pending',
					]
				]
			]
		]
	],
	'releaseLockShouldUnlock' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'release_lock' => true,
			'is_excluded' => false,
			'is_excluded_by_filter' => false,
		],
		'expected' => [
			'urls' => [
				[
					[
						'url' => 'url',
						'status' => 'pending',
						'is_locked' => false,
					]
				],
				[
					[
						'url' => 'url1',
						'status' => 'pending',
						'is_locked' => false,
					]
				]
			]
		]
	],
	'releaseLockWildcardShouldStripAndUnlock' => [
		'config' => [
			'urls' => [
				'https://example.org/url/*',
			],
			'checked_urls' => [
				'https://example.org/url/',
			],
			'release_lock' => true,
			'is_excluded' => false,
			'is_excluded_by_filter' => false,
		],
		'expected' => [
			'urls' => [
				[
					[
						'url' => 'https://example.org/url/',
						'status' => 'pending',
						'is_locked' => false,
					]
				],
			]
		]
	],
	'releaseLockExcludedByFilterShouldDelete' => [
		'config' => [
			'urls' => [
				'url',
			],
			'release_lock' => true,
			'is_excluded' => true,
			'is_excluded_by_filter' => true,
		],
		'expected' => [
			'urls' => [
				[
					'url',
				],
			]
		]
	],
	'excludedByFilterShouldDelete' => [
		'config' => [
			'urls' => [
				'url',
				'url1',
			],
			'is_excluded' => true,
			'is_excluded_by_filter' => true,
		],
		'expected' => [
			'urls' => [
				[
					'url',
				],
				[
					'url1',
				]
			]
		]
	],
];
