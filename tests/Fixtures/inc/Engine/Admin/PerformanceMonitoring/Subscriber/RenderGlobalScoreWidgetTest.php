<?php

use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'freeUserCanAddUrl' => [
		'config' => [
			'customer_data' => (new UserDataGenerator()),
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page1',
					'status' => 'completed',
					'is_mobile' => false,
				],
			],
		],
		'expected' => [
			'html' => file_get_contents( __DIR__ . '/html/output_2_urls_enabled.php' ),
		],
	],
	'freeUserReachedLimit' => [
		'config' => [
			'customer_data' => (new UserDataGenerator()),
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page1',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page2',
					'status' => 'completed',
					'is_mobile' => false,
				],
			],
		],
		'expected' => [
			'html' => file_get_contents( __DIR__ . '/html/output_3_urls_disabled.php' ),
		],
	],
	'advancedUserCanAddUrl' => [
		'config' => [
			'customer_data' => (new UserDataGenerator())->with_pma_active_sku('perf-monitor-advanced'),
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page1',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page2',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page3',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page4',
					'status' => 'completed',
					'is_mobile' => false,
				],
			],
		],
		'expected' => [
			'button_enabled' => true,
			'contains' => [
				'5 page monitored',
				'Add Pages',
				'wpr-pma-global-score-add-url-button',
			],
			'not_contains' => [
				'disabled',
				'wpr-btn-with-tool-tip',
				'Maximum number of URLs reached',
			],
		],
	],
	'mixedStatusUrls' => [
		'config' => [
			'customer_data' => (new UserDataGenerator()),
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page1',
					'status' => 'pending',
					'is_mobile' => false,
				],
				[
					'url' => 'http://example.org/page2',
					'status' => 'failed',
					'is_mobile' => false,
				],
			],
		],
		'expected' => [
			'html' => file_get_contents( __DIR__ . '/html/output_3_urls_loading_disabled.php' ),
		],
	],
];