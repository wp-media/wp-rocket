<?php

$long_array = [
	(object) [
		'type' => 'img',
		'label' => 'lcp',
		'src'   => 'http://example.org/lcp.jpg',
	],
];
$long_array_2 = [
	(object) [
		'type' => 'img',
		'src'   => 'http://example.org/lcp.jpg',
	],
];
for ( $i = 1; $i <= 50; $i++ ) {
	$long_array[] = (object) [
		'label' => 'above-the-fold',
		'type'  => 'img',
		'src'   => 'http://example.org/above-the-fold-' . $i . '.jpg',
	];
	$long_array_2[] = (object) [
		'type' => 'img',
		'src'   => 'http://example.org/above-the-fold-' . $i . '.jpg',
	];
}

return [
	'testShouldBailWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'images'    => json_encode( [] ),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => [],
				'viewport'      => [],
				'last_accessed' => '2024-01-01 00:00:00',
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
			'images'    => json_encode(
				[
					(object) [
						'type'  => 'img',
						'label' => 'lcp',
						'src'   => 'http://example.org/lcp.jpg',
					],
					(object) [
						'type'  => 'img',
						'label' => 'above-the-fold',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
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
			'images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/lcp.jpg',
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenMobile' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/lcp.jpg',
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => true,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				),
				'viewport'      => json_encode(
					[
						(object) [
							'type' => 'img',
							'src'  => 'http://example.org/above-the-fold.jpg',
						],
					],
				),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => true,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldSanitizeLCPAndATF' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/lcp.jpg<script>alert("Test XSS");</script>',
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg<script>alert("Test XSS");</script>',
					],
				]
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpgalert("Test XSS");',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpgalert("Test XSS");',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpgalert("Test XSS");',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpgalert("Test XSS");',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddLongItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'images'    => json_encode(
				$long_array
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode( $long_array_2[0] ),
				'viewport'      => json_encode( array_slice( $long_array_2, 1, 20 ) ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode( $long_array_2[0] ),
				'viewport'      => json_encode( array_slice( $long_array_2, 1, 20 ) ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldNotAddItemToDBWhenNoData' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'images'    => '',
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
];
