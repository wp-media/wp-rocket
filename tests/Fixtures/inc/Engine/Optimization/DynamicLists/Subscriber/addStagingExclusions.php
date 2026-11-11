<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'staging_domains' => [
				'.example.com',
			]
		],
		'expected' => [
			'.example.com',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'.example.com',
		],
		'list' => (object) [
			'staging_domains' => [
				'.example.com',
				'.example2.com',
			]

		],
		'expected' => [
			'.example.com',
			'.example.com',
			'.example2.com',
		],
	],
];
