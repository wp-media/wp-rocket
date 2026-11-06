<?php

return [
	'shouldPurgeSingleUrl' => [
		'config' => [
			'urls' => 'https://example.com/asd',
			'path' => ['/asd'],
			'parsed_url' => [
				'https://example.com/asd' => [
					'scheme' => 'https',
					'host' => 'example.com',
					'path' => '/asd',
				],
			],
		],
		'expected' => [
		],
	],
	'shouldPurgeMultipleUrls' => [
		'config' => [
			'urls' => [
				'https://example.com/asd',
				'https://example.com/qwe',
				'https://example.com/xzc',
				'https://example.com/asd/qwe'
			],
			'path' => ['/asd', '/qwe', '/xzc', '/asd/qwe'],
			'parsed_url' => [
				'https://example.com/asd' => [
					'scheme' => 'https',
					'host' => 'example.com',
					'path' => '/asd',
				],
				'https://example.com/qwe' => [
					'scheme' => 'https',
					'host' => 'example.com',
					'path' => '/qwe',
				],
				'https://example.com/xzc' => [
					'scheme' => 'https',
					'host' => 'example.com',
					'path' => '/xzc',
				],
				'https://example.com/asd/qwe' => [
					'scheme' => 'https',
					'host' => 'example.com',
					'path' => '/asd/qwe',
				]
			],
		],
		'expected' => [
		],
	],
];
