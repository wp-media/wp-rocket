<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'preload_exclusions' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/1',
		],
	],
];
