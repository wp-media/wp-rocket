<?php
return [
	'preloadActivatedShouldChangeStatus' => [
		'config' => [
			'manual_preload' => true,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'https://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
			]
		],
		'expected' => [
			'exists' => true,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
					'is_mobile' => false,
				],
				[
					'url' => 'https://example.org',
					'status' => 'pending',
					'is_mobile' => false,
				],
			]
		]
	],
	'preloadDisabledShouldDoNothing' => [
		'config' => [
			'manual_preload' => false,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'https://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
			]
		],
		'expected' => [
			'exists' => true,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'https://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
			]
		]
	]
];
