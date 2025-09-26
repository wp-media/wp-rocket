<?php

return [
	'testShouldAddPageSuccessfully' => [
		'config' => [
			'post_data' => [
				'page_url' => 'http://example.org/test-page',
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
	'testShouldFailWithEmptyUrl' => [
		'config' => [
			'post_data' => [
				'page_url' => '',
			],
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
			'filters' => [
				'wpr_pm_allow_add_page' => '__return_false',
			],
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
			'mock_http' => false,
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