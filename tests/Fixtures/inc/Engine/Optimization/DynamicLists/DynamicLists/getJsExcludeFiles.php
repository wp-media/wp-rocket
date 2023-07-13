<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'js_exclude_files' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/1',
		],
	],
];
