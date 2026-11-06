<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'cache_ignored_parameters' => [
				'gclid',
			]
		],
		'expected' => [
			'gclid' => 0,
		],
	],
];
