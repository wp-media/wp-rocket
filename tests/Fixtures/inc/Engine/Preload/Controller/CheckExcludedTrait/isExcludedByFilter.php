<?php
return [
	'notMatchingShouldReturnFalse' => [
		'config' => [
			'regexes' => [
				'test',
			],
			'url' => 'http://example.org/wsf'
		],
		'expected' => false
	],
	'emptyFilterShouldReturnFalse' => [
		'config' => [
			'regexes' => [],
			'url' => 'http://example.org/wsf'
		],
		'expected' => false
	],
	'matchingShouldReturnTrue' => [
		'config' => [
			'regexes' => [
				'test',
			],
			'url' => 'http://example.org/test'
		],
		'expected' => true
	]
];
