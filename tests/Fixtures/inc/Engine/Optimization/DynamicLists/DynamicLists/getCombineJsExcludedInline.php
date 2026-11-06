<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'js_excluded_inline' => [
				'nonce',
			],
		],
		'expected' => [
			'nonce',
		],
	],
];
