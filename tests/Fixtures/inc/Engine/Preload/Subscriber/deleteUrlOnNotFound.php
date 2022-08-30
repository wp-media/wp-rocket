<?php
return [
	'preloadShouldRemove' => [
		'config' => [
			'manual_preload' => true,
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
			'url' => 'https://example.org/url',
		],
		'expected' => [
			'exists' => false,
			'data' => [
				[
					'url' => 'https://example.org/url',
				],
			],
		]
	]
];
