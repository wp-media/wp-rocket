<?php
$html     = '<html><head><title></title></head><body><img src="http://example.org/wp-content/uploads/image.jpg"/></body></html>';
$html_expected     = '<html><head><title></title></head><body><img src="http://example.org/wp-content/uploads/image.jpg"/><script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script></body></html>';
$html_lcp = '<html><head><title></title><link rel="preload" as="image" href="http://example.org/wp-content/uploads/image.jpg" fetchpriority="high"></head><body><img fetchpriority="high" src="http://example.org/wp-content/uploads/image.jpg"/></body></html>';

$html_srcset     = '<html><head><title></title></head><body><img src="http://example.org/wp-content/uploads/image.jpg" srcset="http://example.org/wp-content/uploads/image-640x400.jpg 640w" sizes="50vw"/></body></html>';
$html_srcset_lcp = '<html><head><title></title><link rel="preload" as="image" href="http://example.org/wp-content/uploads/image.jpg" imagesrcset="http://example.org/wp-content/uploads/image-640x400.jpg 640w" imagesizes="50vw" fetchpriority="high"></head><body><img fetchpriority="high" src="http://example.org/wp-content/uploads/image.jpg" srcset="http://example.org/wp-content/uploads/image-640x400.jpg 640w" sizes="50vw"/></body></html>';

$html_picture     = '<html><head><title></title></head><body><picture><sources srcset="http://example.org/wp-content/uploads/image-640x400.jpg" media="(max-width: 640px)"><img src="http://example.org/wp-content/uploads/image.jpg"/></picture></body></html>';
$html_picture_lcp = '<html><head><title></title><link rel="preload" as="image" href="http://example.org/wp-content/uploads/image-640x400.jpg" media="(max-width: 640px)" fetchpriority="high"><link rel="preload" as="image" href="http://example.org/wp-content/uploads/image.jpg" fetchpriority="high"></head><body><picture><sources srcset="http://example.org/wp-content/uploads/image-640x400.jpg" media="(max-width: 640px)"><img fetchpriority="high" src="http://example.org/wp-content/uploads/image.jpg"/></picture></body></html>';

$html_bg_img     = '<html><head><title></title></head><body><div></div></body></html>';
$html_bg_img_lcp = '<html><head><title></title><link rel="preload" as="image" href="http://example.org/wp-content/uploads/image.jpg" fetchpriority="high"></head><body><div></div></body></html>';
$html_bg_img_set_lcp = '<html><head><title></title><link rel="preload" as="image" imagesrcset="http://example.org/wp-content/uploads/image.jpg,http://example.org/wp-content/uploads/image-640x400.jpg" fetchpriority="high"></head><body><div></div></body></html>';

return [
	'testShouldReturnSameWhenFeatureDisabled' => [
		'config'   => [
			'filter'                  => false,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'lcp'      => '',
				'viewport' => '',
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => false,
		],
		'html'     => $html,
		'expected' => $html_expected,
	],
	'testShouldReturnSameWhenEmptyRow' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => false,
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => false,
		],
		'html'     => $html,
		'expected' => $html_expected,
	],
	'testShouldReturnSameWhenMissingLCP' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'viewport' => '',
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => false,
		],
		'html'     => $html,
		'expected' => $html_expected,
	],
	'testShouldReturnUpdatedWhenImgLCP' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'status' => 'completed',
				'lcp'      => json_encode( (object) [
					'type' => 'img',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
				] ),
				'viewport' => json_encode( [
					0 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image.jpg',
					],
				] ),
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => true,
		],
		'html'     => $html,
		'expected' => $html_lcp,
	],
	'testShouldReturnUpdatedWhenImgSrcsetLCP' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'status' => 'completed',
				'lcp'      => json_encode( (object) [
					'type' => 'img-srcset',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
					'srcset' => 'http://example.org/wp-content/uploads/image-640x400.jpg 640w',
					'sizes' => '50vw',
				] ),
				'viewport' => json_encode( [] ),
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => true,
		],
		'html'     => $html_srcset,
		'expected' => $html_srcset_lcp,
	],
	'testShouldReturnUpdatedWhenPictureLCP' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'status' => 'completed',
				'lcp'      => json_encode( (object) [
					'type' => 'picture',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
					'sources' => [
						(object) [
							'srcset' => 'http://example.org/wp-content/uploads/image-640x400.jpg',
							'media' => '(max-width: 640px)',
						],
					],
				] ),
				'viewport' => json_encode( [] ),
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => true,
		],
		'html'     => $html_picture,
		'expected' => $html_picture_lcp,
	],
	'testShouldReturnUpdatedBGImgLCP' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'status' => 'completed',
				'lcp'      => json_encode( (object) [
					'type' => 'bg-img',
					'bg_set'  => [
						(object) [
							'src' => 'http://example.org/wp-content/uploads/image.jpg',
						],
					],
				] ),
				'viewport' => json_encode( [] ),
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => true,
		],
		'html'     => $html_bg_img,
		'expected' => $html_bg_img_lcp,
	],
	'testShouldReturnUpdatedBGImgSetLCP' => [
		'config'   => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'lcp'      => json_encode( (object) [
					'status' => 'completed',
					'type' => 'bg-img-set',
					'bg_set'  => [
						(object) [
							'src' => 'http://example.org/wp-content/uploads/image.jpg',
						],
						(object) [
							'src' => 'http://example.org/wp-content/uploads/image-640x400.jpg',
						],
					],
				] ),
				'viewport' => json_encode( [] ),
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
			'row_exists' => true,
		],
		'html'     => $html_bg_img,
		'expected' => $html_bg_img_set_lcp,
	],
];
