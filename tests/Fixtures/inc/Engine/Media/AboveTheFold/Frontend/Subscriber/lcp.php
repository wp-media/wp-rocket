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
];
