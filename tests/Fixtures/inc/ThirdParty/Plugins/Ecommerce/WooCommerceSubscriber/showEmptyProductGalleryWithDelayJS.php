<?php
return [
	'test_data' => [

		'shouldBailoutIfNotAllowed' => [
			'input' => [
				'is_allowed' => false,
			],
			'expected' => [
				'style' => '',
			],
		],

		'shouldBailoutIfNotInProductPage' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => false,
			],
			'expected' => [
				'style' => '',
			],
		],

		'shouldBailoutIfGalleryHasMultipleImages' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => true,
			],
			'expected' => [
				'style' => '',
			],
		],

		'shouldAddStyleIfGalleryHasNoImages' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => false,
			],
			'expected' => [
				'style' => '.woocommerce-product-gallery{ opacity: 1 !important; }',
			],
		],

	],
];
