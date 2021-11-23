<?php
return [
	'test_data' => [

		'shouldBailoutIfNotAllowed' => [
			'input' => [
				'is_allowed' => false,
			],
			'expected' => '',
		],

		'shouldBailoutIfNotInProductPage' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => false,
			],
			'expected' => '',

		],

		'shouldBailoutIfGalleryHasMultipleImages' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => true,
			],
			'expected' => '',
		],

		'shouldAddStyleIfGalleryHasNoImages' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => false,
			],
			'expected' => '<style>.woocommerce-product-gallery{ opacity: 1 !important; }</style>',
		],

	],
];
