<?php
return [
	'testCallActionWhenPreloaded' => [
		'config' => [
			'regexes' => [],
			'manual_preload' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => true,
		],
		'expected' => [
			'url' => 'http://example.org',
			'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
				],
			]
		]
	],
	'testNoCallActionWhenNotPreloaded' => [
		'config' => [
			'regexes' => [],
			'manual_preload' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
		'expected' => [
			'url' => 'http://example.org',
			'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
				],
			]
		]
	],
	'excludedShouldDelete' => [
		'config' => [
			'regexes' => [
				'(.*)example.org(.*)'
			],
			'manual_preload' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
		'expected' => [
			'url' => 'http://example.org',
			'exists' => false,
			'links' => [
				[
					'url' => 'http://example.org',
				],
			]
		]
	],
	'testShouldBailOutWHenPreloadDisabled' => [
		'config' => [
			'regexes' => [],
			'manual_preload' => false,
			'links' => [
			],
			'is_preloaded' => false,
		],
		'expected' => [
			'url' => 'http://example.org',
			'exists' => false,
			'links' => [
				[
					'url' => 'http://example.org',
				],
			],
		],
	],
	'translatedRowShouldBeCompleted'                    => [
		'config'   => [
			'request'        => 'kennisbank/staff-party',
			'map'            => [
				'http://example.org/kennisbank/staff-party' => 'http://example.org/en/knowledge-base/staff-party',
			],
			'regexes'        => [],
			'manual_preload' => true,
			'mobile_files'   => false,
			'links'          => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'in-progress',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/en/knowledge-base/staff-party',
			'exists'        => true,
			'links'         => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'completed',
				],
			],
			'missing'       => [
				[
					'url' => 'http://example.org/kennisbank/staff-party',
				],
			],
		],
	],
	'excludedByInternalSlugShouldDeleteTranslatedRow'   => [
		'config'   => [
			'request'        => 'kennisbank/staff-party',
			'map'            => [
				'http://example.org/kennisbank/staff-party' => 'http://example.org/en/knowledge-base/staff-party',
			],
			'regexes'        => [
				'(.*)kennisbank/staff-party(.*)',
			],
			'manual_preload' => true,
			'mobile_files'   => false,
			'links'          => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'in-progress',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/en/knowledge-base/staff-party',
			'exists'        => false,
			'links'         => [
				[
					'url' => 'http://example.org/en/knowledge-base/staff-party',
				],
			],
			'missing'       => [
				[
					'url' => 'http://example.org/kennisbank/staff-party',
				],
			],
		],
	],
	'excludedByTranslatedSlugShouldDeleteTranslatedRow' => [
		'config'   => [
			'request'        => 'kennisbank/staff-party',
			'map'            => [
				'http://example.org/kennisbank/staff-party' => 'http://example.org/en/knowledge-base/staff-party',
			],
			'regexes'        => [
				'(.*)knowledge-base/staff-party(.*)',
			],
			'manual_preload' => true,
			'mobile_files'   => false,
			'links'          => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'in-progress',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/en/knowledge-base/staff-party',
			'exists'        => false,
			'links'         => [
				[
					'url' => 'http://example.org/en/knowledge-base/staff-party',
				],
			],
			'missing'       => [
				[
					'url' => 'http://example.org/kennisbank/staff-party',
				],
			],
		],
	],
	'excludedShouldAlsoDeleteInternalRow'               => [
		'config'   => [
			'request'        => 'kennisbank/staff-party',
			'map'            => [
				'http://example.org/kennisbank/staff-party' => 'http://example.org/en/knowledge-base/staff-party',
			],
			'regexes'        => [
				'(.*)knowledge-base/staff-party(.*)',
			],
			'manual_preload' => true,
			'mobile_files'   => false,
			'links'          => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'in-progress',
				],
				[
					'url'    => 'http://example.org/kennisbank/staff-party',
					'status' => 'in-progress',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/en/knowledge-base/staff-party',
			'exists'        => false,
			'links'         => [
				[
					'url' => 'http://example.org/en/knowledge-base/staff-party',
				],
				[
					'url' => 'http://example.org/kennisbank/staff-party',
				],
			],
			'missing'       => [],
		],
	],
	'mobilePendingTranslatedRowShouldStayPending'       => [
		'config'   => [
			'request'        => 'kennisbank/staff-party',
			'map'            => [
				'http://example.org/kennisbank/staff-party' => 'http://example.org/en/knowledge-base/staff-party',
			],
			'regexes'        => [],
			'manual_preload' => true,
			'mobile_files'   => true,
			'links'          => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'pending',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/en/knowledge-base/staff-party',
			'exists'        => true,
			'links'         => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party',
					'status' => 'pending',
				],
			],
			'missing'       => [
				[
					'url' => 'http://example.org/kennisbank/staff-party',
				],
			],
		],
	],
	'encodedTranslatedSlugShouldBeCompleted'            => [
		'config'   => [
			'request'        => 'kennisbank/personeelsfeest-%C3%A9',
			'map'            => [
				'http://example.org/kennisbank/personeelsfeest-%C3%A9' => 'http://example.org/en/knowledge-base/staff-party-%C3%A9',
			],
			'regexes'        => [],
			'manual_preload' => true,
			'mobile_files'   => false,
			'links'          => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party-%C3%A9',
					'status' => 'in-progress',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/en/knowledge-base/staff-party-%C3%A9',
			'exists'        => true,
			'links'         => [
				[
					'url'    => 'http://example.org/en/knowledge-base/staff-party-%C3%A9',
					'status' => 'completed',
				],
			],
			'missing'       => [
				[
					'url' => 'http://example.org/kennisbank/personeelsfeest-%C3%A9',
				],
			],
		],
	],
	'noFilterCallbackShouldKeepRequestUrl'              => [
		'config'   => [
			'request'        => 'kennisbank/staff-party',
			'map'            => [],
			'regexes'        => [],
			'manual_preload' => true,
			'mobile_files'   => false,
			'links'          => [
				[
					'url'    => 'http://example.org/kennisbank/staff-party',
					'status' => 'in-progress',
				],
			],
			'is_preloaded'   => true,
		],
		'expected' => [
			'completed_url' => 'http://example.org/kennisbank/staff-party',
			'exists'        => true,
			'links'         => [
				[
					'url'    => 'http://example.org/kennisbank/staff-party',
					'status' => 'completed',
				],
			],
			'missing'       => [],
		],
	],
];
