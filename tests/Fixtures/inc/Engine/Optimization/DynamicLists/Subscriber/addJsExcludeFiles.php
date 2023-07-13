<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'js_exclude_files' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/1',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'/test/',
		],
		'list' => (object) [
			'js_exclude_files' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/',
			'/test/1',
		],
	],
];
