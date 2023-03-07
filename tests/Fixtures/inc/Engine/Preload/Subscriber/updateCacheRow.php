<?php
return [
	'testCallActionWhenPreloaded' => [
		'config' => [
			'query_enabled' => false,
			'params' => [
			],
			'regexes' => [],
			'excluded_params' => [
				'excluded' => 1,
			],
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
			'excluded_params' => [
				'excluded' => 1,
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
			'excluded_params' => [
				'excluded' => 1,
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
			'url' => 'http://example.org?test=1',
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
			'excluded_params' => [
				'excluded' => 1,
			],
			'params' => [
				'lang' => 1
			],
			'links' => [
				[
					'url' => 'http://example.org?lang=1',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
        'expected' => [
			'url' => 'http://example.org?lang=1',
			'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org?lang=1',
					'status' => 'completed',
				],
			],
		],
	],
	'testQueryParamShouldAddWithoutParamWhenFilterEnabledAndExcluded' => [
		'config' => [
			'query_enabled' => true,
			'regexes' => [],
			'excluded_params' => [
				'excluded' => 1,
			],
			'params' => [
				'excluded' => 1
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
			'url' => 'http://example.org',
			'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
				],
			],
		],
	],
	'testQueryParamShouldNotChangeIfNotCachedParamWhenFilterEnabled' => [
		'config' => [
			'query_enabled' => true,
			'regexes' => [],
			'excluded_params' => [
				'excluded' => 1,
			],
			'params' => [
				'test' => 1
			],
			'links' => [
				[
					'url' => 'http://example.org?test=1',
					'status' => 'in-progress',
				],
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
				],
			],
			'is_preloaded' => false,
		],
		'expected' => [
			'url' => 'http://example.org?test=1',
			'exists' => true,
			'links' => [
				[
					'url' => 'http://example.org?test=1',
					'status' => 'in-progress',
				],
				[
					'url' => 'http://example.org',
					'status' => 'in-progress',
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
			'excluded_params' => [
				'excluded' => 1,
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
			'url' => 'http://example.org?test=1',
			'exists' => false,
			'links' => [
				[
					'url' => 'http://example.org?test=1',
					'status' => 'completed',
				],
			]
		]
	],
];
