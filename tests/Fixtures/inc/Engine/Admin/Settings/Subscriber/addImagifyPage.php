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
					'title'            =>  'Image Optimization',
					'menu_description' =>  'Compress your images',
				],
			],
		],
	],
];
