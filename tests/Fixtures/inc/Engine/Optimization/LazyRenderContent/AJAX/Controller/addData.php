<?php

return [
	'testShouldBailoutWhenDBError' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [
						(object) [
							'db47c7d69edcf4565baa182deb470091',
							'db47c7d69edcf4565baa182deb470092',
						],
					]
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [
					(object) [
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
					],
				] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => false,
			'message' => 'error when adding the entry to the database',
		],
	],
	'testShouldAddItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [
						(object) [
							'db47c7d69edcf4565baa182deb470091',
							'db47c7d69edcf4565baa182deb470092',
						],
					]
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [
					(object) [
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
					],
				] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode([
					(object)[
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
						]
					],
				),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenMobile' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'results' => json_encode(
				[
					'lrc' => [
						(object) [
							'db47c7d69edcf4565baa182deb470091',
							'db47c7d69edcf4565baa182deb470092',
						],
					]
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [
					(object) [
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
					],
				] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode([
					(object)[
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
					]
				],
				),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldSanitizeBelowTheFold' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'results' => json_encode(
				[
					'lrc' => [
						(object) [
							'db47c7d69edcf4565<script>alert("Test XSS");</script>baa182deb470091',
							'<script>alert("Test XSS");</script>db47c7d69edcf4565baa182deb470092',
						],
					]
				],
			),
		],
		'expected' => [
			'valid_source' => [
				'db47c7d69edcf4565<script>alert("Test XSS");</script>baa182deb470091' => 'db47c7d69edcf4565alert(Test%20XSS);baa182deb470091',
				'<script>alert("Test XSS");</script>db47c7d69edcf4565baa182deb470092' => 'alert(Test%20XSS);db47c7d69edcf4565baa182deb470092'
			],
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [
					(object) [
						'db47c7d69edcf4565alert(Test%20XSS);baa182deb470091',
						'alert(Test%20XSS);db47c7d69edcf4565baa182deb470092',
					],
				] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => true,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => json_encode([
					(object)[
						'db47c7d69edcf4565alert(Test%20XSS);baa182deb470091',
						'alert(Test%20XSS);db47c7d69edcf4565baa182deb470092',
					]
				]),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldNotAddItemToDBWhenNoData' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.com',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenScriptTimeout' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.com',
			'is_mobile' => false,
			'status'    => 'timeout',
			'results' => json_encode(
				[
					'lrc' => []
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'failed',
				'error_message'  => 'Script timeout',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.com',
				'is_mobile'      => false,
				'status'         => 'failed',
				'error_message'  => 'Script timeout',
				'below_the_fold' => '[]',
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
			],
		],
	],
];
