<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'js_minify_external' => [
				'gist.github.com',
			],
		],
		'expected' => [
			'gist.github.com',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'stats.wordpress.com',
		],
		'list' => (object) [
			'js_minify_external' => [
				'gist.github.com',
			],
		],
		'expected' => [
			'stats.wordpress.com',
			'gist.github.com',
		],
	],
];
