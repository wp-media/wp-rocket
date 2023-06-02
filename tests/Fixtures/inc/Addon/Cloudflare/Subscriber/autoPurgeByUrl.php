<?php

return [
	'testShouldDoNothingWhenNoCap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'page_rule' => true,
			'urls' => [
				'http://example.org',
				'http://example.org/feed/',
			],
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenNoRule' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'page_rule' => false,
			'urls' => [
				'http://example.org',
				'http://example.org/feed/',
			],
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenError' => [
		'config' => [
			'cap' => true,
			'error' => true,
			'page_rule' => true,
			'urls' => [
				'http://example.org',
				'http://example.org/feed/',
			],
		],
		'expected' => null,
	],
	'testShouldPurgeWhenHasRule' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'page_rule' => true,
			'urls' => [
				'http://example.org',
				'http://example.org/feed/',
			],
		],
		'expected' => 'expected',
	],
];
