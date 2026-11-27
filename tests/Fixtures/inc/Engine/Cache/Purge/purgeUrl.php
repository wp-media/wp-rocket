<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html' => '',
					],
				],
			],
		],
	],
	'test_data' => [
		'non-Latin URL stored with uppercase percent-encoding' => [
			'url'             => 'https://example.org/%D7%97%D7%A0%D7%95%D7%AA',
			'expected_suffix' => '/example.org/%d7%97%d7%a0%d7%95%d7%aa',
		],
		'non-Latin URL stored with lowercase percent-encoding' => [
			'url'             => 'https://example.org/%d7%97%d7%a0%d7%95%d7%aa',
			'expected_suffix' => '/example.org/%d7%97%d7%a0%d7%95%d7%aa',
		],
		'ASCII URL is unaffected' => [
			'url'             => 'https://example.org/shop',
			'expected_suffix' => '/example.org/shop',
		],
	],
];
