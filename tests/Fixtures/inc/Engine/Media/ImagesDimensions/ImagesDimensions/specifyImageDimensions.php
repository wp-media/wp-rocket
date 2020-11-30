<?php

$simple_html_with_local_image = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Title</title>
</head>
<body>
	<img src="https://example.org/wp-content/themes/image.jpg">
</body>
</html>
HTML;

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
HTML;


return [
	'vfs_dir'   => '/',
	'structure' => [
		'wp-content' => [
			'themes' => [
				'image.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/Engine/Media/ImagesDimensions/empty.jpg" )
			]
		]
	],

	'test_data' => [

		'shouldNotChangeHTMLWhenOptionANDFilterDisabled' => [
			'html' => $simple_html_with_local_image,
			'config' => [
				'images_dimensions' => false,
				'rocket_specify_image_dimensions_filter' => false,
			],
			'expected' => $simple_html_with_local_image
		],

		'shouldNotChangeHTMLWhenNoImages' => [
			'html' => $simple_html_without_images,
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true
			],
			'expected' => $simple_html_without_images
		],

		'shouldNotChangeHTMLWithImageHasWidthHeightAttributes' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://example.org/wp-content/themes/image.jpg" width="100" height="100"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html><html><body><img src="https://example.org/wp-content/themes/image.jpg" width="100" height="100"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasAttribute_data-lazy-original' => [
			'html' => '<!DOCTYPE html>
<html>
<body>
	<img src="https://example.org/wp-content/themes/image.jpg" data-lazy-original="">
</body>
</html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html>
<html>
<body>
	<img src="https://example.org/wp-content/themes/image.jpg" data-lazy-original="">
</body>
</html>'
		],

		'shouldNotChangeHTMLWhenHasAttribute_data-no-image-dimensions' => [
			'html' => '<!DOCTYPE html>
<html>
<body>
	<img src="https://example.org/wp-content/themes/image.jpg" data-no-image-dimensions="">
</body>
</html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html>
<html>
<body>
	<img src="https://example.org/wp-content/themes/image.jpg" data-no-image-dimensions="">
</body>
</html>'
		],

		'shouldNotChangeHTMLWhenHasImageWithoutSrcAttribute' => [
			'html' => '<!DOCTYPE html><html><body><img anothersrc="https://example.org/wp-content/themes/image.jpg"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,

			],
			'expected' => '<!DOCTYPE html><html><body><img anothersrc="https://example.org/wp-content/themes/image.jpg"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasExternalValidImageWithDistantFilterDisabled' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/wp-content/themes/wp-rocket/assets/images/support-photo-2017.jpg"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'external' => true,
				'rocket_specify_image_dimensions_for_distant_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/wp-content/themes/wp-rocket/assets/images/support-photo-2017.jpg"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasExternalNotFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/image.jpg"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'external' => true,
				'rocket_specify_image_dimensions_for_distant_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/image.jpg"></body></html>'
		],

		'shouldChangeHTMLWhenHasExternalFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://v3b4d4f5.rocketcdn.me/wp-content/themes/wp-rocket/assets/images/support-photo-2017.jpg"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'external' => true,
				'rocket_specify_image_dimensions_for_distant_filter' => true
			],
			'expected' => '<!DOCTYPE html><html><body><img width="655" height="257" src="https://v3b4d4f5.rocketcdn.me/wp-content/themes/wp-rocket/assets/images/support-photo-2017.jpg"></body></html>'
		],

		'shouldNotChangeHTMLWhenHasInternalNotFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://example.org/wp-content/themes/image-notfound.jpg"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><img src="https://example.org/wp-content/themes/image-notfound.jpg"></body></html>'
		],

		'shouldChangeHTMLWhenHasInternalFoundImage' => [
			'html' => '<!DOCTYPE html><html><body><img src="https://example.org/wp-content/themes/image.jpg"></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><img width="1" height="1" src="https://example.org/wp-content/themes/image.jpg"></body></html>'
		],

		'shouldNotChangeHTMLWithImageInsidePictureWithoutFilter' => [
			'html' => '<!DOCTYPE html><html><body><picture><img src="https://example.org/wp-content/themes/image.jpg"></picture></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
			],
			'expected' => '<!DOCTYPE html><html><body><picture><img src="https://example.org/wp-content/themes/image.jpg"></picture></body></html>'
		],

		'shouldChangeHTMLWithImageInsidePictureWithFilter' => [
			'html' => '<!DOCTYPE html><html><body><picture><img src="https://example.org/wp-content/themes/image.jpg"></picture></body></html>',
			'config' => [
				'images_dimensions' => true,
				'rocket_specify_image_dimensions_filter' => true,
				'internal' => true,
				'rocket_specify_dimension_images_inside_pictures_filter' => false
			],
			'expected' => '<!DOCTYPE html><html><body><picture><img width="1" height="1" src="https://example.org/wp-content/themes/image.jpg"></picture></body></html>'
		],

	]
];
