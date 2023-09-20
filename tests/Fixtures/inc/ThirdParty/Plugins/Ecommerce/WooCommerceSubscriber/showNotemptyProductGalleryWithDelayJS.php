<?php
return [
	'test_data' => [

		'shouldBailoutIfNotAllowed' => [
			'input' => [
				'is_allowed' => false,
			],
			'expected' => [
				'excluded' => [],
			],
		],

		'shouldBailoutIfNotInProductPage' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => false,
			],
			'expected' => [
				'excluded' => [],
			],
		],

		'shouldBailoutIfGalleryHasNoImages' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => false,
			],
			'expected' => [
				'excluded' => []
			],
		],

		'shouldExcludeScriptsIfGalleryHasSomeImagesWithWP5.6' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => true,
				'wp_version' => '5.6',
			],
			'expected' => [
				'excluded' => [
					'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
					'/woocommerce/assets/js/zoom/jquery.zoom(.min)?.js',
					'/woocommerce/assets/js/photoswipe/',
					'/woocommerce/assets/js/flexslider/jquery.flexslider(.min)?.js',
					'/woocommerce/assets/js/frontend/single-product(.min)?.js',
					'/jquery-migrate(.min)?.js',
				],
			],
		],

		'shouldExcludeScriptsIfGalleryHasSomeImagesWithWP5.7' => [
			'input' => [
				'is_allowed' => true,
				'in_product_page' => true,
				'has_images' => true,
				'wp_version' => '5.7',
			],
			'expected' => [
				'excluded' => [
					'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
					'/woocommerce/assets/js/zoom/jquery.zoom(.min)?.js',
					'/woocommerce/assets/js/photoswipe/',
					'/woocommerce/assets/js/flexslider/jquery.flexslider(.min)?.js',
					'/woocommerce/assets/js/frontend/single-product(.min)?.js',
				],
			],
		],

	],
];
