<?php
return [
	'testNoCallActionWhenPreloaded' => [
		'config' => [
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
	'testCallActionWhenNotPreloaded' => [
		'config' => [
			'links' => [
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
	]
];
