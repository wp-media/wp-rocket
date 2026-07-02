<?php

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

	'testShouldRemovePageSuccessfully' => [
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
		],
		'expected' => [
			'is_error'               => false,
			'success'                => true,
			'hook_fired'             => true,
			'database_entries_after' => 0,
		],
	],

	'testShouldRemoveBothMobileAndDesktopRows' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/dual-page',
			],
			'existing_items' => [
				[
					'url'       => 'https://example.com/dual-page',
					'title'     => 'Dual Page Mobile',
					'is_mobile' => true,
					'job_id'    => 'test_mobile',
					'status'    => 'completed',
					'score'     => 80,
					'data'      => '{"status":"complete"}',
				],
				[
					'url'       => 'https://example.com/dual-page',
					'title'     => 'Dual Page Desktop',
					'is_mobile' => false,
					'job_id'    => 'test_desktop',
					'status'    => 'completed',
					'score'     => 90,
					'data'      => '{"status":"complete"}',
				],
			],
		],
		'expected' => [
			'is_error'               => false,
			'success'                => true,
			'hook_fired'             => true,
			'hook_count'             => 1,
			'database_entries_after' => 0,
		],
	],

	'testShouldFailWhenUrlNotMonitored' => [
		'config'   => [
			'has_permission' => true,
			'input'          => [
				'url' => 'https://example.com/not-monitored',
			],
			'existing_items' => [
				[
					'url'       => 'https://example.com/other-page',
					'title'     => 'Other Page',
					'is_mobile' => true,
					'job_id'    => 'test_other',
					'status'    => 'completed',
					'score'     => 70,
					'data'      => '{"status":"complete"}',
				],
			],
		],
		'expected' => [
			'is_error'               => false,
			'success'                => false,
			'error'                  => 'URL is not currently being monitored.',
			'hook_fired'             => false,
			'database_entries_after' => 1,
		],
	],
];
