<?php
return [
	'testCallActionWhenPreloaded' => [
		'config' => [
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
	'excludedShouldDelete' => [
		'config' => [
			'regexes' => [
				'(.*)example.org(.*)'
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
			'exists' => false,
			'links' => [
				[
					'url' => 'http://example.org',
				],
			]
		]
	]
];
