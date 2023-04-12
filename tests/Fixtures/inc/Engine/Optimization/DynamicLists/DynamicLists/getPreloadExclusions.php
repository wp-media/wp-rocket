<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'preload_exclusions' => [
				'test.github.com',
			],
		],
		'expected' => [
			'test.github.com',
		],
	],
];
