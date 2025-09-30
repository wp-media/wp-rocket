<?php
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'testShouldAddPageSuccessfully' => [
		'config' => [
			'post_data' => [
				'page_url' => 'http://example.org/test-page',
			],
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => (new UserDataGenerator()),
			'mock_http' => true,
		],
		'expected' => [
			'success' => true,
			'database_entries' => 2,
			'hook_fired' => true,
			'response_data' => [
				'id' => null, // Will be generated
				'html' => null, // Will be generated
				'global_score_data' => null, // Will be generated
				'remaining_urls' => null, // Will be generated
				'can_add_pages' => true,
			],
		],
	],
	'testShouldFailWithEmptyUrl' => [
		'config' => [
			'post_data' => [
				'page_url' => '',
			],
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => (new UserDataGenerator()),
			'mock_http' => false,
		],
		'expected' => [
			'success' => false,
			'error_message' => 'No url provided',
		],
	],
	'testShouldFailWithUrlLimitReached' => [
		'config' => [
			'post_data' => [
				'page_url' => 'https://example.com/test-page',
			],
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => 1,
				],
				[
					'url' => 'http://example.org/page2',
					'status' => 'completed',
					'is_mobile' => 1,
				],
				[
					'url' => 'http://example.org/page3',
					'status' => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => (new UserDataGenerator()),
			'mock_http' => false,
		],
		'expected' => [
			'success' => false,
			'error_message' => 'Maximum number of URLs reached',
		],
	],
	'testShouldFailWithUnreachableUrl' => [
		'config' => [
			'post_data' => [
				'page_url' => 'https://external-site.com/page',
			],
			'rows' => [
				[
					'url' => 'http://example.org',
					'status' => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => (new UserDataGenerator()),
			'mock_http' => true,
		],
		'expected' => [
			'success' => false,
			'error_message' => 'Url does not resolve to a valid page',
		],
	],
	'testShouldSucceedWithExternalUrl' => [
		'config' => [
			'post_data' => [
				'page_url' => 'https://example.org',
			],
			'mock_http' => true,
		],
		'expected' => [
			'success' => true,
			'database_entries' => 1,
			'hook_fired' => true,
			'response_data' => [
				'id' => null, // Will be generated
				'html' => null, // Will be generated
				'global_score_data' => null, // Will be generated
				'remaining_urls' => null, // Will be generated
				'can_add_pages' => true,
			],
		],
	],
];