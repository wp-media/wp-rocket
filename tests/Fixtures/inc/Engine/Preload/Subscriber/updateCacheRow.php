<?php
return [
	'testCallActionWhenPreloaded' => [
		'config' => [
			'query_enabled' => false,
			'params' => [
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
			'links' => [
				[
					'url' => 'http://example.org/?test=1',
					'status' => 'completed',
				],
			]
		]
	],
];
