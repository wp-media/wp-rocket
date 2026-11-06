<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'js_excluded_inline' => [
				'nonce',
			],
		],
		'expected' => [
			'nonce',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'document.write',
		],
		'list' => (object) [
			'js_excluded_inline' => [
				'nonce',
			],
		],
		'expected' => [
			'document.write',
			'nonce',
		],
	],
];
