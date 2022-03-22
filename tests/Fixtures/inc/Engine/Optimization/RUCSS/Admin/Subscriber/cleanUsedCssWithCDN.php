<?php

$items = [
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => true,
	],
];

return [

	// Test data.
	'test_data' => [
		'shouldNotTruncateUnusedCSSDueToMissingSettings' => [
			'input' => [
				'items'             => $items,
				'settings'          => [],
				'old_settings'      => [],
			],
			'expected' => [
				'truncated' => false,
			],
		],
		'shouldNotTruncateUnusedCSSDueToSettings' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 0,
					'cdn'               => 0,
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
				],
			],
			'expected' => [
				'truncated' => false,
			],
		],
		'shouldTruncateUnusedCSSWhenCDNChanges' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 0,
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
				],
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 0,
			],
		],
		'shouldTruncateUnusedCSSWhenCNamesChanges' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn.example.org'
					],
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn2.example.org'
					],
				],
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 0,
			],
		],

		'shouldTruncateUnusedCSSWhenZonesChanges' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn.example.org'
					],
					'cdn_zone'          => [
						'all'
					],
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn.example.org'
					],
					'cdn_zone'          => [
						'css'
					],
				],
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 0,
			],
		],

		'shouldDeleteCompletedUnusedCSS' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 0,
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
				],
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 10,
			],
		],
	],
];
