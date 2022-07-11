<?php
return [
	'testCallActionWhenPreloaded' => [
		'config' => [
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'pending',
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
			'links' => [
				[
					'url' => 'http://example.org',
					'status' => 'pending',
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
	]
];
