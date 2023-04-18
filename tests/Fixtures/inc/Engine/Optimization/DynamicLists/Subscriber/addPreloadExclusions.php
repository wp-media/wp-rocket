<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'preload_exclusions' => [
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
			'preload_exclusions' => [
				'/test/1',
			],
		],
		'expected' => [
			'/test/',
			'/test/1',
		],
	],
];
