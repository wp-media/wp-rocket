<?php

return [
	'shouldReturnEmptyArrayWhenNotSet' => [
		'config' => [
			'lists' => (object) [
				'staging_domains' => [
					'.example.com',
				],
			]
		],
		'expected' => [],
	],
	'shouldReturnArrayWhenSet' => [
		'config' => [
			'lists' => (object) [
				'external_font_exclusions' => [
					'cdnjs.cloudflare.com',
					'unpkg.com',
					'maxcdn.bootstrapcdn.com',
				],
			]
		],
		'expected' => [
			'cdnjs.cloudflare.com',
			'unpkg.com',
			'maxcdn.bootstrapcdn.com',
		],
	],
];
