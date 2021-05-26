<?php
return [
	'test_data' => [
		'shouldHideImagifyWhenLicenseAndWhiteLabel'     => [
			'config'   => [
				'white_label' => true,
				'license'     => true,
			],
			'expected' => [],
		],
		'shouldHideImagifyWhenNoLicenseAndWhiteLabel'   => [
			'config'   => [
				'white_label' => false,
				'license'     => true,
			],
			'expected' => [],
		],
		'shouldHideImagifyWhenLicenseAndNoWhiteLabel'   => [
			'config'   => [
				'white_label' => true,
				'license'     => false,
			],
			'expected' => [],
		],
		'shouldHideImagifyWhenNoLicenseAndNoWhiteLabel' => [
			'config'   => [
				'white_label' => false,
				'license'     => false,
			],
			'expected' => [
				'imagify' => [
					'id'               => 'imagify',
					'title'            => __( 'Image Optimization', 'rocket' ),
					'menu_description' => __( 'Compress your images', 'rocket' ),
				],
			],
		],
	],
];
