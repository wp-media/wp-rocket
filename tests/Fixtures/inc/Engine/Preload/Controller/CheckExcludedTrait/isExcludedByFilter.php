<?php
return [
	'notMatchingShouldReturnFalse' => [
		'config' => [
			'queries' => 'test=1&a=2',
			'url_with_query' => 'url_with_query',
			'regex_with_query' => 'regex_with_query',
			'regexes' => [
				'test',
			],
			'url' => 'http://example.org/wsf'
		],
		'expected' => false
	],
	'emptyFilterShouldReturnFalse' => [
		'config' => [
			'queries' => 'test=1&a=2',
			'url_with_query' => 'url_with_query',
			'regex_with_query' => 'url_with_query',
			'regexes' => [],
			'url' => 'http://example.org/wsf'
		],
		'expected' => false
	],
	'matchingShouldReturnTrue' => [
		'config' => [
			'queries' => 'test=1&a=2',
			'url_with_query' => 'url_with_query',
			'regex_with_query' => 'url_with_query',
			'regexes' => [
				'test',
			],
			'url' => 'http://example.org/test'
		],
		'expected' => true
	],
];
