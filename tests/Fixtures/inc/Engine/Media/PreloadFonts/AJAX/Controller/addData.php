<?php

return [
	'testShouldAddItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode([
				'preload_fonts' => [
					'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
					'https://fonts.googleapis.com/css?family=Roboto',
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
					],
				),
				'last_accessed'  => '2025-02-02 00:00:00',
				'created_at'     => '2025-02-02 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
					],
				),
				'created_at'     => '2025-02-02 00:00:00',
				'last_accessed'  => '2025-02-02 00:00:00',
			],
		],
	],
	'testShouldOnlyAddItemsNotExcluded' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode([
				'preload_fonts' => [
					'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
					'https://fonts.googleapis.com/css?family=Roboto',
					'https://fonts.gstatic.com/s/montserrat/v25/JTUQjIg1_i6t8kCHKm459WxZ.woff2',
					'https://fonts.gstatic.com/s/lato/v24/S6uyw4BMUTPHjx4wWw.woff2',
				]
			]),
			'exclusions' => [
				'Montserrat',
				'https://fonts.gstatic.com/s/lato/v24/S6uyw4BMUTPHjx4wWw.woff2',
			]
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
					],
				),
				'last_accessed'  => '2025-02-02 00:00:00',
				'created_at'     => '2025-02-02 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
					],
				),
				'created_at'     => '2025-02-02 00:00:00',
				'last_accessed'  => '2025-02-02 00:00:00',
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
					'preload_fonts' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'fonts'         => [],
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
				'preload_fonts' => [
					'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
					'https://fonts.googleapis.com/css?family=Roboto',
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
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
				'preload_fonts' => [
					'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
					'https://fonts.googleapis.com/css?family=Roboto',
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
					],
				),
				'last_accessed'  => '2025-02-02 00:00:00',
				'created_at'     => '2025-02-02 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'error_message'  => '',
				'fonts' => json_encode(
					[
						'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
						'https://fonts.googleapis.com/css?family=Roboto',
					],
				),
				'created_at'     => '2025-02-02 00:00:00',
				'last_accessed'  => '2025-02-02 00:00:00',
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
					'preload_fonts' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'fonts'         => '[]',
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
				'fonts'         => '[]',
				'created_at'    => '2025-01-01 00:00:00',
				'last_accessed' => '2025-01-01 00:00:00',
			],
		],
	]
];
