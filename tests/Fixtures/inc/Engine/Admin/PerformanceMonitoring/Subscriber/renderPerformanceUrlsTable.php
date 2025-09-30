<?php

return [
	'freeUser' => [
		'config' => [
			'is_free' => true,
			'items' => ['item1', 'item2'],
			'score' => [
				'status'    => 'in-progress',
				'pages_num' => 1,
				'score'     => 85,
			],
			'remaining_urls' => 2,
			'pma_addon_limit' => 3,
			'license_data' => [],
			'has_credits' => true,
		],
		'expected' => [
			'items'        => ['item1', 'item2'],
			'global_score' => [
				'status'    => 'in-progress',
				'pages_num' => 1,
				'score'     => 85,
			],
			'remaining_urls' => 2,
			'pma_addon_limit' => 3,
			'upgrade_url'    => '',
			'can_add_pages'  => true,
			'show_quota_banner' => false,
			'is_free' => true,
		],
	],
	'freeUserWithNoCredits' => [
		'config' => [
			'is_free' => true,
			'items' => ['item1'],
			'score' => [
				'status'    => 'completed',
				'pages_num' => 1,
				'score'     => 92,
			],
			'remaining_urls' => 2,
			'pma_addon_limit' => 3,
			'license_data' => [],
			'has_credits' => false,
		],
		'expected' => [
			'items'        => ['item1'],
			'global_score' => [
				'status'    => 'completed',
				'pages_num' => 1,
				'score'     => 92,
			],
			'remaining_urls' => 2,
			'pma_addon_limit' => 3,
			'upgrade_url'    => '',
			'can_add_pages'  => true,
			'show_quota_banner' => true, // No credits left
			'is_free' => true,
		],
	],
	'premiumUser' => [
		'config' => [
			'is_free' => false,
			'items' => ['item1', 'item2', 'item3'],
			'score' => [
				'status'    => 'completed',
				'pages_num' => 3,
				'score'     => 88,
			],
			'remaining_urls' => 7,
			'pma_addon_limit' => 10,
			'license_data' => ['btn_url' => 'https://upgrade.example.com'],
			'call_count_expectations' => [
				'get_remaining_url_count' => 1,
				'get_pma_addon_limit' => 1,
			],
		],
		'expected' => [
			'items'        => ['item1', 'item2', 'item3'],
			'global_score' => [
				'status'    => 'completed',
				'pages_num' => 3,
				'score'     => 88,
			],
			'remaining_urls' => 7,
			'pma_addon_limit' => 10,
			'upgrade_url'    => 'https://upgrade.example.com',
			'can_add_pages'  => true,
			'show_quota_banner' => false,
			'is_free' => false,
		],
	],
];