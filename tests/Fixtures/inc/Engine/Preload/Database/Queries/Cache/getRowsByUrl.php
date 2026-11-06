<?php

$full_result = (object) [
	'url' => 'http://example.com',
	'status' => 'pending',
	'last_accessed' => '838:59:59.000000'
];

return [
	'resultFoundShouldResult' => [
		'config' => [
			'url' => 'http://example.com',
			'result' => $full_result
		],
		'expected' => $full_result
	],
	'nothingFoundShouldReturnNothing' => [
		'config' => [
			'url' => 'http://example.com',
			'result' => false,
		],
		'expected' => false,
	]
];
