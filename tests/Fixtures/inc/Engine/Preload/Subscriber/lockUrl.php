<?php
return [
	'doesNotExistShouldCreate' => [
		'config' => [
			'url' => 'http://example.org',
			'data' => []
		],
		'expected' => [
			'data' => [
				[
					'url' => 'http://example.org',
					'is_locked' => true
				]
			]
		]
	],
	'existShouldUpdate' => [
		'config' => [
			'url' => 'http://example.org',
			'data' => [
				[
					'url' => 'http://example.org'
				]
			]
		],
		'expected' => [
			'data' => [
				[
					'url' => 'http://example.org',
					'is_locked' => true
				]
			]
		]
	]
];
