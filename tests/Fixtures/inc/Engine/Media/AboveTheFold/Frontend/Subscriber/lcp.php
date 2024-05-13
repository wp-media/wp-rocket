<?php

$html_input = file_get_contents(__DIR__ . '/HTML/input.html');
$html_output = file_get_contents(__DIR__ . '/HTML/output.html');
$html_output_with_preload = file_get_contents(__DIR__ . '/HTML/output_w_preload.html');
$html_output_with_beacon = file_get_contents(__DIR__ . '/HTML/output_w_beacon.html');
$html_input_with_bg_image_lcp = file_get_contents(__DIR__ . '/HTML/input_w_bg_image_lcp.html');
$html_output_with_bg_image_lcp = file_get_contents(__DIR__ . '/HTML/output_w_bg_image_lcp.html');
$html_input_with_picture_img_lcp = file_get_contents(__DIR__ . '/HTML/input_w_picture_img_lcp.html');
$html_output_with_picture_img_lcp = file_get_contents(__DIR__ . '/HTML/output_w_picture_img_lcp.html');
$html_input_with_img_lcp = file_get_contents(__DIR__ . '/HTML/input_w_img_lcp.html');
$html_output_with_img_lcp = file_get_contents(__DIR__ . '/HTML/output_w_img_lcp.html');
$html_input_with_relative_img_lcp = file_get_contents(__DIR__ . '/HTML/input_with_relative_img_lcp.html');
$html_input_with_absolute_img_lcp = file_get_contents(__DIR__ . '/HTML/input_with_absolute_img_lcp.html');
$html_input_with_domain_img_lcp = file_get_contents(__DIR__ . '/HTML/input_lcp_image.html');

return [
	'test_data' => [
		'shouldAddBeaconToPage' => [
			'config' => [
				'html' => $html_input,
				'row' => null,
			],
			'expected' => $html_output_with_beacon,
		],
		'shouldNotAddBeaconToPage' => [
			'config' => [
				'html' => $html_input,
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp'      => json_encode( (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image.jpg',
					] ),
					'viewport' => json_encode( [
						0 => (object) [
							'type' => 'img',
							'src'  => 'http://example.org/wp-content/uploads/image2.jpg',
						],
					] ),
				],
			],
			'expected' => $html_output_with_preload,
		],
		'shouldNotAddBeaconToPageWhenLcpFailed' => [
			'config' => [
				'html' => $html_input,
				'row' => [
					'status' => 'failed',
					'url' => 'http://example.org',
					'lcp'      => json_encode( (object) [
					] ),
					'viewport' => json_encode( [
					] ),
				],
			],
			'expected' => $html_output,
		],
		'shouldPreloadLcpResponsiveImgset' => [
			'config' => [
				'html' => file_get_contents(__DIR__ . '/HTML/input_lcp_bg_responsive_imgset_template.php'),
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode( (object) [
						'type' => 'bg-img-set',
						'bg_set'  => [
							['src' => "http://example.org/wp-content/rocket-test-data/images/lcp/testavif.avif 1dppx"],
							['src' => "http://example.org/wp-content/rocket-test-data/images/lcp/testwebp.webp 2dppx"]
						]
					]),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_bg_responsive_imgset_template.php'),
		],
		'shouldPreloadLcpResponsiveWebkit' => [
			'config' => [
				'html' => file_get_contents(__DIR__ . '/HTML/input_lcp_bg_responsive_webkit_template.php'),
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode( (object) [
						'type' => 'bg-img-set',
						'bg_set'  => [
							['src' => "https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8 1dppx"],
							['src' => "https://rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg 2dppx"]
						]
					]),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_bg_responsive_webkit_template.php'),
		],
		'shouldPreloadLcpLayeredBackground' => [
			'config' => [
				'html' => file_get_contents(__DIR__ . '/HTML/input_lcp_layered_bg.php'),
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode( (object) [
						'type' => 'bg-img',
						'bg_set'  => [
							['src' => "https://fastly.picsum.photos/id/976/200/300.jpg?hmac=s1Uz9fgJv32r8elfaIYn7pLpQXps7X9oYNwC5XirhO8"],
							['src' => "https://rocketlabsqa.ovh/wp-content/rocket-test-data/images/fixtheissue.jpg"]
						]
					]),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_layered_bg.php'),
		],
		'shouldPreloadLcpSingleBackground' => [
			'config' => [
				'html' => file_get_contents(__DIR__ . '/HTML/input_lcp_single_bg.php'),
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode( (object) [
						'type' => 'bg-img',
						'bg_set'  => [
							['src' => "http://example.org/wp-content/rocket-test-data/images/lcp/testavif.avif"],
						]
					]),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_single_bg.php'),
		],
		'shouldPreloadLcpResponsiveImage' => [
			'config' => [
				'html' => file_get_contents(__DIR__ . '/HTML/input_lcp_responsive.php'),
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode( (object) [
						'type' => 'img-srcset',
						'src' => 'wolf.jpg',
						"srcset" => "wolf_400px.jpg 400w, wolf_800px.jpg 800w, wolf_1600px.jpg 1600w",
						"sizes" => "50vw",
					]),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_responsive.php'),
		],
		'shouldApplyFetchPriorityToReturnRelativeImage' => [
			'config' => [
				'html' => $html_input_with_relative_img_lcp,
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp'      => json_encode( (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/sample_relative_image.jpg',
					] ),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_with_relative_img_lcp.php'),
		],
		'shouldApplyFetchPriorityToAbsoluteImage' => [
			'config' => [
				'html' => $html_input_with_absolute_img_lcp,
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp'      => json_encode( (object) [
						'type' => 'img',
						'src'  => 'http://example.com/wp-content/uploads/sample_absolute_image.jpg',
					] ),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_with_absolute_img_lcp.php'),
		],
		'shouldApplyFetchPriorityToImageWithDomain' => [
			'config' => [
				'html' => $html_input_with_domain_img_lcp,
				'row' => [
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp'      => json_encode( (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/sample_url_image.png',
					] ),
					'viewport' => json_encode ( [] ),
				],
			],
			'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_image.php'),
		],
	],
	'shouldPreloadPictureTag' => [
		'config' => [
			'html' => file_get_contents(__DIR__ . '/HTML/input_lcp_picture.php'),
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'lcp' => json_encode( (object) [
					'type' => 'picture',
					'src' => 'large_cat.jpg',
					'sources' => [
						[
							'srcset' => 'small_cat.jpg',
							'media' => '(max-width: 400px)'
						],
						[
							'srcset' => 'medium_cat.jpg',
							'media' => '(max-width: 800px)'
						]
					]
				]),
				'viewport' => json_encode ( [] ),
			],
		],
		'expected' => file_get_contents(__DIR__ . '/HTML/output_lcp_picture.php'),
	],
	'shouldNotApplyFetchPriorityToTheWrongElement' => [
		'config' => [
			'html' => $html_input_with_bg_image_lcp,
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'lcp'      => json_encode( (object) [
					'type' => 'img',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
				] ),
				'viewport' => json_encode( [
					0 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image2.jpg',
					],
					1 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image3.jpg',
					],
				] ),
			],
		],
		'expected' => $html_output_with_bg_image_lcp,
	],
	'shouldApplyFetchPriorityToTheImgTagWithPictureElement' => [
		'config' => [
			'html' => $html_input_with_picture_img_lcp,
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'lcp'      => json_encode( (object) [
					'type' => 'img',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
				] ),
				'viewport' => json_encode( [
					0 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image2.jpg',
					],
					1 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image3.jpg',
					],
				] ),
			],
		],
		'expected' => $html_output_with_picture_img_lcp,
	],
	'shouldApplyFetchPriorityToTheImgElement' => [
		'config' => [
			'html' => $html_input_with_img_lcp,
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'lcp'      => json_encode( (object) [
					'type' => 'img',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
				] ),
				'viewport' => json_encode( [
					0 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image2.jpg',
					],
					1 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image3.jpg',
					],
				] ),
			],
		],
		'expected' => $html_output_with_img_lcp,
	],
	'shouldNotDoAnythingIfNoLcp' => [
		'config' => [
			'html' => $html_input,
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'lcp'      => 'not found',
				'viewport' => json_encode( [
				] ),
			],
		],
		'expected' => $html_output,
	],
];
