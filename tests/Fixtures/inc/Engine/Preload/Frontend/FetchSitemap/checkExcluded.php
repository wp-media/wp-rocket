<?php
return [
	'excludedShouldReturnTrue' => [
		'config' => [
			'excluded_urls' => 'url',
			'url' => 'url'
		],
		'expected' => true
	],
	'notExcludedShouldReturnFalse' => [
		'config' => [
			'excluded_urls' => 'uri',
			'url' => 'url'
		],
		'expected' => false
	]
];
