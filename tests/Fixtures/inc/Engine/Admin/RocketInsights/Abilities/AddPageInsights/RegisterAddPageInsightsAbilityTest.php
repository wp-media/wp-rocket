<?php

// Answers every mocked request: a successful SaaS submission, which the page fetch accepts as a 200 page too.
$ok = [
	'response' => [ 'code' => 200 ],
	'body'     => wp_json_encode( [ 'success' => true, 'uuid' => 'test-uuid', 'code' => 200 ] ),
];

// The URL "does not resolve", so no submission follows.
$not_found = [
	'response' => [ 'code' => 404, 'message' => 'Not Found' ],
	'body'     => '',
];

$saas_url = 'http://localhostperformance/';

return [
	'testShouldReturnWPErrorWhenNoPermissions' => [
		'config'   => [
			'has_permission' => false,
			'input'          => [
				'url' => 'https://example.com/test-page',
			],
			'existing_items' => [],
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
			'http'           => [
				'https://example.com/test-page' => $ok,
				$saas_url                      => $ok,
			],
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
			'http'           => [
				'https://example.com/existing-page' => $ok,
			],
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => false,
			'error'      => 'URL has already been submitted for performance monitoring.',
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
			'http'           => [
				'https://example.com/non-existent-page' => $not_found,
			],
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
			'http'           => [
				'https://example.org/' => $ok,
				$saas_url              => $ok,
			],
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
			'http'           => [
				'https://example.com/new-page' => $ok,
				$saas_url                     => $ok,
			],
			'url_limit'      => 10,
		],
		'expected' => [
			'is_error'   => false,
			'success'    => true,
			'hook_fired' => true,
		],
	],
];
