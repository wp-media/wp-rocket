<?php

return [
	'testShouldReturnWPErrorWhenNoPermissions' => [
		'config'   => [
			'has_permission' => false,
			'input'          => [
				'url' => 'https://example.com/test-page',
			],
			'existing_items' => [],
			'mock_http'      => false,
		],
		'expected' => [
			'is_error'   => true,
			'success'    => false,
			'hook_fired' => false,
		],
	],

	'testShouldAddPageSuccessfully' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/test-page',
			],
			'existing_items' => [],
			'mock_http'      => true,
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => true,
			'hook_fired' => true,
		],
	],

	'testShouldFailWhenUrlAlreadySubmitted' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/existing-page',
			],
			'existing_items' => [
				[
					'url'       => 'https://example.com/existing-page',
					'title'     => 'Existing Page',
					'is_mobile' => true,
					'job_id'    => 'test_123',
					'status'    => 'completed',
					'score'     => 85,
					'data'      => '{"status":"complete"}',
				],
			],
			'mock_http'      => true,
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => false,
			'error'      => '',
			'hook_fired' => false,
		],
	],

	'testShouldFailWhenUrlDoesNotResolve' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/non-existent-page',
			],
			'existing_items' => [],
			'mock_http'      => false,
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => false,
			'error'      => 'Url does not resolve to a valid page.',
			'hook_fired' => false,
		],
	],

	'testShouldAddHomepageWithCorrectTitle' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.org/',
			],
			'existing_items' => [],
			'mock_http'      => true,
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => true,
			'hook_fired' => true,
		],
	],

	'testShouldAddMultiplePages' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/new-page',
			],
			'existing_items' => [
				[
					'url'       => 'https://example.com/page1',
					'title'     => 'Page 1',
					'is_mobile' => true,
					'job_id'    => 'test_1',
					'status'    => 'completed',
					'score'     => 90,
					'data'      => '{"status":"complete"}',
				],
				[
					'url'       => 'https://example.com/page2',
					'title'     => 'Page 2',
					'is_mobile' => true,
					'job_id'    => 'test_2',
					'status'    => 'completed',
					'score'     => 85,
					'data'      => '{"status":"complete"}',
				],
			],
			'mock_http'      => true,
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => true,
			'hook_fired' => true,
		],
	],
];
