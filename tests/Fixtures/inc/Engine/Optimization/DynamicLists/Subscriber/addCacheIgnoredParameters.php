<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'cache_ignored_parameters' => [
				'gclid',
			]
		],
		'expected' => [
			'gclid' => 0,
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'fbclid' => 0
		],
		'list' => (object) [
			'cache_ignored_parameters' => [
				'gclid',
			]
		],
		'expected' => [
			'fbclid' => 0,
			'gclid'  => 0,
		],
	],
];
