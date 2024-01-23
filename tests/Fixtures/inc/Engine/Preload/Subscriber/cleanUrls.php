<?php
return [
	'urlsShouldChangeStatusUrls' => [
		'config' => [
			'manual_preload' => true,
			'urls' => [
				'https://example.org/url',
				'https://example.org/url1',
			],
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
			],
		],
		'expected' => [
			'exists' => false,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
					'is_mobile' => false,
				],
				[
					'url' => 'https://example.org/url1',
					'status' => 'pending',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
			],
		]
	],
];
