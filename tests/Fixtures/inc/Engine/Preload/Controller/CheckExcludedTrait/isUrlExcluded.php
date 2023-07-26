<?php
return [
	'testShouldReturnFalse' => [
		'config' => [
			'queries' => 'test=1&a=2',
			'url' => 'url',
			'url_with_query' => 'url_with_query',
			'regex_with_query' => 'regex_with_query',
			'excluded_queries' => [
				'b' => 1
			],
			'regexes' => [
				'test',
			],
		],
		'expected' => false
	],
	'testExcludedQueryShouldReturnTrue' => [
		'config' => [
			'url_with_query' => 'url_with_query',
			'regex_with_query' => 'regex_with_query',
			'queries' => 'test=1&a=2',
			'url' => 'url',
			'excluded_queries' => [
				'test' => 1
			],
			'regexes' => [
				'test',
			],
		],
		'expected' => true
	],
	'testExcludedByFilterShouldReturnTrue' => [
		'config' => [
			'url_with_query' => 'url_with_query',
			'regex_with_query' => 'regex_with_query',
			'queries' => 'test=1&a=2',
			'url' => 'url',
			'regexes' => [
				'url',
			],
			'excluded_queries' => [
				'c' => 1
			],
		],
		'expected' => true
	],
];
