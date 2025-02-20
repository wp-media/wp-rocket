<?php

return [
	'testShouldAddItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode([
				'domains' => [
					'https://example-domain-1.com/',
					'https://example-domain-2.com/',
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'domains' => json_encode(
					[
						'https://example-domain-1.com/',
						'https://example-domain-2.com/',
					],
				),
				'created_at'     => '2025-02-18 00:00:00',
				'last_accessed'  => '2025-02-18 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'domains' => json_encode(
					[
						'https://example-domain-1.com/',
						'https://example-domain-2.com/',
					],
				),
				'created_at'     => '2025-02-18 00:00:00',
				'last_accessed'  => '2025-02-18 00:00:00',
			],
		],
	],
	'testShouldBailWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'domains' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'domains' => [],
				'created_at'    => '2025-02-02 00:00:00',
				'last_accessed' => '2025-02-02 00:00:00',
			],
			'result'  => false,
			'message' => 'not allowed',
		],
	],
	'testShouldBailoutWhenDBError' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode([
				'domains' => [
					'https://fonts.googleapis.org',
					'https://fonts.googleapis.com',
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'domains' => json_encode(
					[
						'https://fonts.googleapis.org',
						'https://fonts.googleapis.com',
					],
				),
				'last_accessed'  => '2025-02-02 00:00:00',
				'created_at'     => '2025-02-02 00:00:00',
				'error_message'  => ''
			],
			'result'  => false,
			'message' => 'error when adding the entry to the database',
		],
	],
	'testShouldAddItemToDBWhenMobile' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'results' => json_encode([
				'domains' => [
					'https://example-domain.org',
					'https://example-domain.ng',
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'domains' => json_encode(
					[
						'https://example-domain.org',
						'https://example-domain.ng',
					],
				),
				'last_accessed'  => '2025-02-18 00:00:00',
				'created_at'     => '2025-02-18 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'error_message'  => '',
				'domains' => json_encode(
					[
						'https://example-domain.org',
						'https://example-domain.ng',
					],
				),
				'created_at'     => '2025-02-18 00:00:00',
				'last_accessed'  => '2025-02-18 00:00:00',
			],
		],
	],
	'testShouldNotAddItemToDBWhenNoData' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'domains' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'domains'       => '[]',
				'last_accessed' => '2025-01-01 00:00:00',
				'error_message' => '',
				'created_at'    => '2025-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'domains'       => '[]',
				'created_at'    => '2025-01-01 00:00:00',
				'last_accessed' => '2025-01-01 00:00:00',
			],
		],
	]
];
