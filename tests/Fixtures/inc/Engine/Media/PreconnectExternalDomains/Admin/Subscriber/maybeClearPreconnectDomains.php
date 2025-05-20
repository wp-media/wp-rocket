<?php


return [
	'testShouldClearPreconnectDomains' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => false,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldNotClearPreconnectDomains' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 1,
		],
	],
];
