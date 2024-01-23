<?php

return [
	'whenPostHookShouldChangeStatusUrls' => [
		'config' => [
			'manual_preload' => true,
			'hook' => 'after_rocket_clean_post',
			'object' => new WP_Post((object) [
				'ID' => 1,
				'post_type' => 'post'
			]),
			'urls' => [
				'https://example.org/url',
				'https://example.org/url1',
			],
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
				],
				[
					'url' => 'http://example.org',
					'status' => 'completed',
				],
			],
			'lang' => 'en'
		],
		'expected' => [
			'exists' => false,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
				],
				[
					'url' => 'https://example.org/url1',
					'status' => 'pending',
				],
				[
					'url' => 'http://example.org',
					'status' => 'pending',
				],
			],
		]
	],
	'whenTermHookShouldChangeStatusUrls' => [
		'config' => [
			'manual_preload' => true,
			'hook' => 'after_rocket_clean_term',
			'object' => new WP_Term((object) [
				'ID' => 1,
				'post_type' => 'post'
			]),
			'urls' => [
				'https://example.org/url',
				'https://example.org/url1',
			],
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
				],
				[
					'url' => 'http://example.org',
					'status' => 'completed',
				],
			],
			'lang' => 'en'
		],
		'expected' => [
			'exists' => true,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
				],
				[
					'url' => 'https://example.org/url1',
					'status' => 'pending',
				],
				[
					'url' => 'http://example.org',
					'status' => 'pending',
				],
			],
		]
	]
];
