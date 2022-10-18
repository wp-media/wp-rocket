<?php
return [
	'testCallActionWhenPreloaded' => [
		'config' => [
			'query_enabled' => false,
			'params' => [
			],
			'regexes' => [],
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
			'query_enabled' => false,
			'params' => [
			],
			'regexes' => [],
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
	'testQueryParamShouldDoNothingWhenFilterDisabled' => [
		'config' => [
			'query_enabled' => false,
			'regexes' => [],
			'params' => [
				'test' => 1
			],
			'links' => [
				[
					'url' => 'http://example.org/?test=1',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
		'expected' => [
			'url' => 'http://example.org/?test=1',
      		'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
				],
			]
		]
	],
	'testQueryParamShouldAdaptWhenFilterEnabled' => [
		'config' => [
			'query_enabled' => true,
			'regexes' => [],
			'params' => [
				'test' => 1
			],
			'links' => [
				[
					'url' => 'http://example.org/?test=1',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
        'expected' => [
			'url' => 'http://example.org/?test=1',
			'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org/?test=1',
					'status' => 'completed',
				],
			],
		],
	],
	'excludedShouldDelete' => [
		'config' => [
			'query_enabled' => true,
			'regexes' => [
				'(.*)example.org(.*)'
			],
			'params' => [
				'test' => 1
			],
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
		'expected' => [
			'url' => 'http://example.org/?test=1',
			'exists' => false,
			'links' => [
				[
					'url' => 'http://example.org/?test=1',
					'status' => 'completed',
				],
			]
		]
	],
];
