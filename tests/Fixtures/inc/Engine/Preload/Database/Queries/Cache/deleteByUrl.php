<?php

$result_one = (object) [
	'id' => 1
];

$result_two = (object) [
	'id' => 2
];

return [
	'notResultsShouldNotDelete' => [
		'config' => [
			'url' => 'http://example.com',
			'results' => [],
		],
		'expected' => false,
	],
	'resultsWithOneErrorShouldReturnFalse' => [
		'config' => [
			'url' => 'http://example.com',
			'results' => [
				$result_one,
				$result_two,
			],
			'delete_id_one' => 1,
			'delete_id_two' => 2,
			'delete_return_one' => true,
			'delete_return_two' => true,
		],
		'expected' => true,
	],
	'resultsWithoutErrorShouldReturnTrue' => [
		'config' => [
			'url' => 'http://example.com',
			'results' => [
				$result_one,
				$result_two,
			],
			'delete_id_one' => 1,
			'delete_id_two' => 2,
			'delete_return_one' => true,
			'delete_return_two' => false,
		],
		'expected' => false,
	]
];
