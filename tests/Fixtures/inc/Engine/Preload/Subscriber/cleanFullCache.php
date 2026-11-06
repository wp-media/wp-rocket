<?php
return [
	'preloadActivatedShouldChangeStatus' => [
		'config' => [
			'manual_preload' => true,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'completed',
				],
				[
					'url' => 'https://example.org',
					'status' => 'completed',
				],
			]
		],
		'expected' => [
			'exists' => true,
			'data' => [
				[
					'url' => 'https://example.org/url',
					'status' => 'pending',
				],
				[
					'url' => 'https://example.org',
					'status' => 'pending',
				],
			]
		]
	],
];
