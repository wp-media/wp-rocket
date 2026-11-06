<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'js_minify_external' => [
				'gist.github.com',
			],
		],
		'expected' => [
			'gist.github.com',
		],
	],
];
