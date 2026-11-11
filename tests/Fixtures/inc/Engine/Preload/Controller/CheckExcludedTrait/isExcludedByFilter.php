<?php
return [
	'notMatchingShouldReturnFalse' => [
		'config' => [
			'url' => 'http://example.org/wsf',
			'isPrivate' => false
		],
		'expected' => false
	],
	'matchingShouldReturnTrue' => [
		'config' => [
			'url' => 'http://example.org/test',
			'isPrivate' => true
		],
		'expected' => true
	],
];
