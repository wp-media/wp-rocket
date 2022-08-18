<?php

return [
	'noElementsShouldReturnFalse' => [
		'config' => [
			'url' => 'http://example.org',
			'result' => 0,
		],
		'expected' => false
	],
	'elementsShouldReturnTrue' => [
		'config' => [
			'url' => 'http://example.org',
			'result' => 10,
		],
		'expected' => true
	]
];
