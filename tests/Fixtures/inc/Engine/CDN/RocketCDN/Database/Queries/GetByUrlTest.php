<?php

return [
	'test_data' => [
		'findWithExactUrl' => [
			'config' => [
				'items' => [
					[
						'title' => 'Example page',
						'url'   => 'https://example.org/some-page',
					],
				],
				'search_url' => 'https://example.org/some-page',
			],
			'expected' => [
				'found' => true,
				'title' => 'Example page',
				'url'   => 'https://example.org/some-page',
			],
		],
		'findWithTrailingSlash' => [
			'config' => [
				'items' => [
					[
						'title' => 'Example page',
						'url'   => 'https://example.org/some-page',
					],
				],
				'search_url' => 'https://example.org/some-page/',
			],
			'expected' => [
				'found' => true,
				'title' => 'Example page',
				'url'   => 'https://example.org/some-page',
			],
		],
		'returnFalseWhenMissing' => [
			'config' => [
				'items' => [
					[
						'title' => 'Example page',
						'url'   => 'https://example.org/some-page',
					],
				],
				'search_url' => 'https://example.org/unknown-page',
			],
			'expected' => [
				'found' => false,
			],
		],
	],
];
