<?php

return [
	'shouldReturnArray' => [
		'config' => [
			'lists' => (object) [
				'staging_domains' => [
					'.example.com',
				],
			]
		],
		'expected' => [
			'lists' => (object) [
				'staging_domains' => [
					'.example.com',
				],
			],
		],
	],
];
