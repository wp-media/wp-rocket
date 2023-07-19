<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'exclude_js_files' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/1',
		],
	],
];
