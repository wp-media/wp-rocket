<?php
return [
	'apiError'   => [
		'config'   => [
			'create_free_error'        => 'Internal Server Error',
			'subscription_status_code' => 404,
		],
		'expected' => [
			'is_error' => true,
		],
	],

	'apiSuccess' => [
		'config'   => [
			'free_pages'               => [
				[
					'url'   => 'http://example.org/',
					'title' => 'Home',
				],
			],
			'create_free_code'         => 200,
			'create_free_body'         => [
				'success' => true,
				'data'    => [
					'code'      => 'cdn_task_enqueued',
					'task_id'   => 'task_abc_123',
					'cdn_token' => 'newtoken12345678901234567890123456789',
				],
			],
			'subscription_status_code' => 404,
			'website_search_code'      => 404,
		],
		'expected' => [
			'is_error' => false,
			'token'    => 'newtoken12345678901234567890123456789',
		],
	],
];
