<?php
return [
	'testShouldReturnFalse' => [
		'config' => [
			'queries' => 'test=1&a=2',
			'url' => 'url',
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
