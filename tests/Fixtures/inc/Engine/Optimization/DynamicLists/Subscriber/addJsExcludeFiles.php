<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'exclude_js_files' => [
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
			'exclude_js_files' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/',
			'/test/1',
		],
	],
];
