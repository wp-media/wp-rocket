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
			'lcp_images'    => json_encode( [] ),
			'results' => json_encode(
				[
					'lcp' => []
				],
			),
		],
		'expected' => [
			'images_valid_sources' => [],
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
			'lcp_images'    => json_encode(
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
			'results' => json_encode(
				[
					'lcp' => [
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
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [],
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
				'error_message' => '',
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
			'lcp_images'    => json_encode(
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
			'results' => json_encode(
				[
					'lcp' => [
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
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [],
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
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
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
			'lcp_images'    => json_encode(
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
			'results' => json_encode(
				[
					'lcp' => [
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
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [],
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
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => true,
				'status'        => 'completed',
				'error_message' => '',
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
			'lcp_images'    => json_encode(
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
			'results' => json_encode(
				[
					'lcp' => [
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
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [
				'http://example.org/lcp.jpg<script>alert("Test XSS");</script>' => 'http://example.org/lcp.jpgscriptalert(Test%20XSS);/script',
				'http://example.org/above-the-fold.jpg<script>alert("Test XSS");</script>' => 'http://example.org/above-the-fold.jpgscriptalert(Test%20XSS);/script'
			],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpgscriptalert(Test%20XSS);/script',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpgscriptalert(Test%20XSS);/script',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpgscriptalert(Test%20XSS);/script',
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpgscriptalert(Test%20XSS);/script',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldSanitizeArrayLCPAndATF' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'bg-img',
						'src'   => '',
						'bg_set' => [
							[
								'src' => 'http://example.org/anotherlcp.jpg'
							],
							[
								'src' => 'http://example.org/anotherlcp2.jpg'
							]
						]
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'bg-img',
							'src'   => '',
							'bg_set' => [
								[
									'src' => 'http://example.org/anotherlcp.jpg'
								],
								[
									'src' => 'http://example.org/anotherlcp2.jpg'
								]
							]
						],
						(object) [
							'label' => 'above-the-fold',
							'type'  => 'img',
							'src'   => 'http://example.org/above-the-fold.jpg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [
			],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'bg-img',
						'bg_set' => [
							[
								'src'  => 'http://example.org/anotherlcp.jpg'
							],
							[
								'src'  => 'http://example.org/anotherlcp2.jpg'
							],
						],
						'src'  => ''
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => json_encode(
					(object) [
						'type' => 'bg-img',
						'bg_set' => [
							[
								'src'  => 'http://example.org/anotherlcp.jpg'
							],
							[
								'src'  => 'http://example.org/anotherlcp2.jpg'
							],
						],
						'src'  => ''
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
	'testShouldSanitizeImageSrcWithLCPAndATFArray' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'bg-img-set',
						'src'   => [
							[
								'src' => 'http://example.org/lcp.jpg'
							],
							[
								'src' => 'http://example.org/random.jpg'
							]
						],
						'bg_set' => [
							[
								'src' => 'http://example.org/anotherlcp.jpg'
							],
							[
								'src' => 'http://example.org/anotherlcp2.jpg'
							]
						]
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'bg-img-set',
							'src'   => [
								[
									'src' => 'http://example.org/lcp.jpg'
								],
								[
									'src' => 'http://example.org/random.jpg'
								]
							],
							'bg_set' => [
								[
									'src' => 'http://example.org/anotherlcp.jpg'
								],
								[
									'src' => 'http://example.org/anotherlcp2.jpg'
								]
							]
						],
						(object) [
							'label' => 'above-the-fold',
							'type'  => 'img',
							'src'   => 'http://example.org/above-the-fold.jpg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'bg-img-set',
						'bg_set' => [
							[
								'src'  => 'http://example.org/anotherlcp.jpg'
							],
							[
								'src'  => 'http://example.org/anotherlcp2.jpg'
							],
						],
						'src'   => ''
					],
				),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => json_encode(
					(object) [
						'type' => 'bg-img-set',
						'bg_set' => [
							[
								'src'  => 'http://example.org/anotherlcp.jpg'
							],
							[
								'src'  => 'http://example.org/anotherlcp2.jpg'
							],
						],
						'src'   => ''
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
	'testShouldAddLongItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				$long_array
			),
			'results' => json_encode(
				[
					'lcp' => $long_array,
				],
			),
			'filetype' => [
				'ext' => 'jpg',
				'type' => 'image/jpeg',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode( $long_array_2[0] ),
				'viewport'      => json_encode( array_slice( $long_array_2, 1, 20 ) ),
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
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
			'lcp_images'    => '',
			'results' => json_encode(
				[
					'lcp' => []
				],
			),
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldReturnNotFound' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'src'   => "",
						'bg_set' => [],
						'type' => ''
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => '',
						'src'   => '',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'src'   => "",
							'bg_set' => [],
							'type' => ''
						],
						(object) [
							'label' => 'above-the-fold',
							'type'  => '',
							'src'   => '',
						],
					]
				],
			),
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],

	'testShouldAddItemToDBWhenScriptError' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => '',
			'results' => json_encode(
				[
					'lcp' => []
				],
			),
			'status'    => 'script_error',
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'failed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => 'Script error',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'failed',
				'error_message' => 'Script error',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenScriptTimeout' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => '',
			'results' => json_encode(
				[
					'lcp' => []
				],
			),
			'status'    => 'timeout',
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'failed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => 'Script timeout',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'failed',
				'error_message' => 'Script timeout',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],

	'testShouldBailoutWithNotValidImages1' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/file.php?url=img.jpg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'img',
							'src'   => 'http://example.org/file.php?url=img.jpg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'php',
				'type' => false,
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldBailoutWithNotValidImages2' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/file.js?url=img.jpg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'img',
							'src'   => 'http://example.org/file.js?url=img.jpg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'js',
				'type' => 'application/javascript',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldBailoutWithNotValidImages3' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/file.php#url=img.jpg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'img',
							'src'   => 'http://example.org/file.php#url=img.jpg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'php',
				'type' => 'application/php',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldBailoutWithNotValidImages4' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'chrome-extension://extension-hash/path/to/image/x.svg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'img',
							'src'   => 'chrome-extension://extension-hash/path/to/image/x.svg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'svg',
				'type' => 'image/svg+xml',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldBailoutWithNotValidImages5' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'bg-img',
						'src'   => 'linear-gradient(160deg, rgb(255, 255, 255) 0%, rgb(248, 246, 243) 100%)',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'bg-img',
							'src'   => 'linear-gradient(160deg, rgb(255, 255, 255) 0%, rgb(248, 246, 243) 100%)',
						],
					]
				],
			),
			'filetype' => [
				'ext' => false,
				'type' => false,
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => 'not found',
				'viewport'      => '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
	'testShouldAddItemToDBWhenSvgWithHttpProtocol' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'lcp_images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/path/to/images/image.svg',
					],
				]
			),
			'results' => json_encode(
				[
					'lcp' => [
						(object) [
							'label' => 'lcp',
							'type'  => 'img',
							'src'   => 'http://example.org/path/to/images/image.svg',
						],
					]
				],
			),
			'filetype' => [
				'ext' => 'svg',
				'type' => 'image/svg+xml',
			],
			'allowed_mime_types' => [
				'jpg|jpeg|jpe'                 => 'image/jpeg',
				'gif'                          => 'image/gif',
				'png'                          => 'image/png',
				'bmp'                          => 'image/bmp',
				'tiff|tif'                     => 'image/tiff',
				'webp'                         => 'image/webp',
				'avif'                         => 'image/avif',
				'ico'                          => 'image/x-icon',
				'heic'                         => 'image/heic',
			],
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/path/to/images/image.svg',
					],
				),
				'viewport' 		=> '[]',
				'last_accessed' => '2024-01-01 00:00:00',
				'error_message' => '',
			],
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => json_encode(
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/path/to/images/image.svg',
					],
				),
				'viewport' 		=> '[]',
				'last_accessed' => '2024-01-01 00:00:00',
			],
		],
	],
];
