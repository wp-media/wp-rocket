<?php

$simple_html_with_local_image = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Title</title>
</head>
<body>
	<img src="http://example.org/wp-content/themes/image.jpg">
</body>
</html>
HTML
;

$simple_html_without_images = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Title</title>
</head>
<body>
	<div>
	<h1>HI this is WP Rocket!</h1>
	</div>
</body>
</html>
HTML
;


return [
	'vfs_dir'   => '/',
	'structure' => [
		'wp-content' => [
			'themes' => [
				'image.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImageDimensions/empty.jpg" ),
				'100x100image.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImageDimensions/100x100image.jpg"),
				'500x300image.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImageDimensions/500x300image.jpg"),
				'image.svg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImageDimensions/image.svg" ),
				'viewbox.svg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImageDimensions/viewbox.svg" ),
			]
		],
		'main' => [
			'image.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImageDimensions/empty.jpg" )
		]
	],

	'test_data' => [

		'shouldNotChangeHTMLWhenNoImages' => [
			'html' => $simple_html_without_images,
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true
			],
			'expected' => $simple_html_without_images
		],

		'shouldNotChangeHTMLWithImageHasWidthHeightAttributes' => [
			'html' => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image.jpg" width="100" height="100"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image.jpg" width="100" height="100"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasAttribute_data-lazy-original' => [
			'html' => '<!DOCTYPE html>
<html>
<body>
	<img src="http://example.org/wp-content/themes/image.jpg" data-lazy-original="">
</body>
</html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html>
<html>
<body>
	<img src="http://example.org/wp-content/themes/image.jpg" data-lazy-original="">
</body>
</html>'
		],

		'shouldNotChangeHTMLWhenHasAttribute_data-no-image-dimensions' => [
			'html' => '<!DOCTYPE html>
<html>
<body>
	<img src="http://example.org/wp-content/themes/image.jpg" data-no-image-dimensions="">
</body>
</html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html>
<html>
<body>
	<img src="http://example.org/wp-content/themes/image.jpg" data-no-image-dimensions="">
</body>
</html>'
		],

		'shouldNotChangeHTMLWhenHasImageWithoutSrcAttribute' => [
			'html' => '<!DOCTYPE html><html><body><img anothersrc="http://example.org/wp-content/themes/image.jpg"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html><html><body><img anothersrc="http://example.org/wp-content/themes/image.jpg"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasExternalValidImageWithDistantFilterDisabled' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/wp-content/themes/V4/assets/images/blocks/support/support@2x.png"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'external' => true,
				'rocket_specify_image_dimensions_for_distant_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/wp-content/themes/V4/assets/images/blocks/support/support@2x.png"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasExternalNotFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/image.jpg"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'external' => true,
				'rocket_specify_image_dimensions_for_distant_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/image.jpg"></body></html>'
		],

		'shouldChangeHTMLWhenHasExternalFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://wp-rocket.me/wp-content/themes/V4/assets/images/blocks/support/support@2x.png"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'external' => true,
				'rocket_specify_image_dimensions_for_distant_filter' => true
			],
			'expected' => '<!DOCTYPE html><html><body><img width="1240" height="763" src="https://wp-rocket.me/wp-content/themes/V4/assets/images/blocks/support/support@2x.png"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasInternalNotFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image-notfound.jpg"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image-notfound.jpg"></body></html>'
		],

		'shouldChangeHTMLWhenHasInternalFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image.jpg"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><img width="1" height="1" src="http://example.org/wp-content/themes/image.jpg"></body></html>'
		],

		'shouldNotChangeHTMLWithImageInsidePictureWithoutFilter' => [
			'html' => '<!DOCTYPE html>
<html>
<body>
<picture>
<img src="http://example.org/wp-content/themes/image.jpg">
</picture>
</body>
</html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html>
<html>
<body>
<picture>
<img src="http://example.org/wp-content/themes/image.jpg">
</picture>
</body>
</html>'
		],

		'shouldChangeHTMLWithImageInsidePictureWithFilter' => [
			'html' => '<!DOCTYPE html><html><body><picture><img src="http://example.org/wp-content/themes/image.jpg"></picture></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
				'rocket_specify_dimension_skip_pictures_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><picture><img width="1" height="1" src="http://example.org/wp-content/themes/image.jpg"></picture></body></html>'
		],

		'shouldChangeHTMLWithRelativeImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="/wp-content/themes/image.jpg"><img src="/main/image.jpg"><img src="http://example.org/main/image.jpg"></body></html>',
			'config' => [
				'site_url' => 'http://example.org/wp/',
				'home_url' => 'http://example.org/',
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
				'rocket_specify_dimension_skip_pictures_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><img width="1" height="1" src="/wp-content/themes/image.jpg"><img width="1" height="1" src="/main/image.jpg"><img width="1" height="1" src="http://example.org/main/image.jpg"></body></html>'
		],

		'shouldAddMissingHeightWhenOnlyWidthSpecified' => [
			'html'     => '<!DOCTYPE html><html><body><img width="75" src="http://example.org/wp-content/themes/100x100image.jpg"></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><img width="75" height="75" src="http://example.org/wp-content/themes/100x100image.jpg"></body></html>',
		],

		'shouldAddMissingWidthWhenOnlyHeightSpecified' => [
			'html'     => '<!DOCTYPE html><html><body><img height="75" src="http://example.org/wp-content/themes/100x100image.jpg"></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><img width="75" height="75" src="http://example.org/wp-content/themes/100x100image.jpg"></body></html>',
		],

		'shouldAddIntegerValuesWhenRatioResultsInFloat' => [
			'html'     => '<!DOCTYPE html><html><body><img height="172" src="http://example.org/wp-content/themes/500x300image.jpg"></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><img width="287" height="172" src="http://example.org/wp-content/themes/500x300image.jpg"></body></html>',
		],

		'shouldNotChangeHTMLWhenNonNumericValueGivenForDimension' => [
			'html'     => '<!DOCTYPE html><html><body><img height="not-a-number" src="http://example.org/wp-content/themes/500x300image.jpg"></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><img height="not-a-number" src="http://example.org/wp-content/themes/500x300image.jpg"></body></html>',
		],

		'shouldAddDimensionsWhenSVG' => [
			'html'     => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image.svg"></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><img width="100" height="150" src="http://example.org/wp-content/themes/image.svg"></body></html>',
		],

		'shouldAddDimensionsFromVieBoxWhenSVGWithNoDimensionsAttributes' => [
			'html'     => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/viewbox.svg"></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><img width="100" height="150" src="http://example.org/wp-content/themes/viewbox.svg"></body></html>',
		],

		'testShouldNotAddDimensionsWhenImageInsideAScript' => [
			'html'     => '<!DOCTYPE html><html><body><script><img src="http://example.org/wp-content/themes/viewbox.svg"></script></body></html>',
			'config'   => [
				'image_dimensions'                       => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected'     => '<!DOCTYPE html><html><body><script><img src="http://example.org/wp-content/themes/viewbox.svg"></script></body></html>',
		],
		'shouldNotChangeImgCustomHeightAttr' => [
			'html' => '<!DOCTYPE html><html><body><img data-height="189" src="http://example.org/wp-content/themes/image.jpg"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><img width="1" height="1" data-height="189" src="http://example.org/wp-content/themes/image.jpg"></body></html>'
		],
		'shouldNotChangeImgCustomWidthAttr' => [
			'html' => '<!DOCTYPE html><html><body><img custom-width="189" src="http://example.org/wp-content/themes/image.jpg"></body></html>',
			'config' => [
				'image_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><img width="1" height="1" custom-width="189" src="http://example.org/wp-content/themes/image.jpg"></body></html>'
		],
	],
	'shouldChangeImgWithEmptyWidthAndHeight' => [
		'html' => '<!DOCTYPE html><html><body><img src="http://example.org/wp-content/themes/image.jpg" height="" width=""></body></html>',
		'config' => [
			'image_dimensions' => true,
			'rocket_specify_image_dimensions_filter' => true,
			'internal' => true,
		],
		'expected' => '<!DOCTYPE html><html><body><img width="1" height="1" src="http://example.org/wp-content/themes/image.jpg"></body></html>'
	],
	'shouldAddMissingHeightWhenOnlyWidthSpecifiedWithoutoutes' => [
		'html'     => '<!DOCTYPE html><html><body><img width=75 src="http://example.org/wp-content/themes/100x100image.jpg"></body></html>',
		'config'   => [
			'image_dimensions'                       => true,
			'rocket_specify_image_dimensions_filter' => true,
			'internal' => true,
		],
		'expected'     => '<!DOCTYPE html><html><body><img width=75 height="75" src="http://example.org/wp-content/themes/100x100image.jpg"></body></html>',
	],
];
