<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => [
			'.example.com',
		],
		'expected' => [
			'.example.com',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'.example.com',
		],
		'list' =>[
			'.example.com',
			'.example2.com',
		],
		'expected' => [
			'.example.com',
			'.example.com',
			'.example2.com',
		],
	],
];
