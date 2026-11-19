<?php

$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Addon/WebP/Subscriber/original.html' );
$updated = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Addon/WebP/Subscriber/updated.html' );
$nomatch = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Addon/WebP/Subscriber/nomatch.html' );
$not_smaller = '<img src="https://example.org/wp-content/uploads/2019/09/larger-webp.png" alt="" /><img src="https://example.org/wp-content/uploads/2019/09/same-size.png" alt="" />';

return [
	'vfs_dir' => 'wp-content/',
	'structure' => [
		'wp-content' => [
			'uploads' => [
				'2019' => [
					'09' => [
						'one-image.png' => 'original image',
						'one-image.webp' => 'webp',
						'one-image-60x60.png' => 'original image',
						'larger-webp.png' => 'png',
						'larger-webp.webp' => 'larger webp',
						'same-size.png' => 'same',
						'same-size.png.webp' => 'size',
						'no-original.webp' => 'webp',
					],
				],
				'2017' => [
					'02' => [
						'apple-touch-icon.png' => 'original image',
						'apple-touch-icon.png.webp' => 'webp',
						'favicon-32x32.png' => 'original image',
						'favicon-32x32.webp' => 'webp',
						'mstile-144x144.png' => 'original image',
						'mstile-144x144.webp' => 'webp',
						'stats-php.gif' => 'original image',
						'stats-php.gif.webp' => 'webp',
					],
				],
			],
		],
	],
	'test_data' => [
		'shouldReturnSameHtmlWhenCacheIsDisabledByOption' => [
			'config' => [
				'webp' => 0,
				'filter_disable' => false,
				'headers' => 'webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $original,
			'expected' => $original,
		],
		'shouldReturnSameHtmlWhenWebpCacheIsDisabledByFilter' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => true,
				'headers' => 'webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $original,
			'expected' => $original,
		],
		'shouldReturnSameHtmlWhenNoWebpHeader' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/*',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $original,
			'expected' => $original,
		],
		'shouldReturnHtmlWithCommentWhenNoFileExtensions' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/webp',
				'filter_ext' => [],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $original,
			'expected' => $original . '<!-- Rocket no webp -->',
		],
		'shouldReturnHtmlWithCommentWhenNoAttributeNames' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [],
			],
			'original' => $original,
			'expected' => $original . '<!-- Rocket no webp -->',
		],
		'shouldReturnHtmlWithCommentWhenNoMatches' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $nomatch,
			'expected' => $nomatch . '<!-- Rocket no webp -->',
		],
		'shouldReturnModifiedHtml' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $original,
			'expected' => $updated . '<!-- Rocket has webp -->',
		],
		'shouldNotUseWebpNotSmallerThanOriginal' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => $not_smaller,
			'expected' => $not_smaller . '<!-- Rocket no webp -->',
		],
		'shouldUseWebpWhenOriginalSizeIsUnknown' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ],
			],
			'original' => '<img src="https://example.org/wp-content/uploads/2019/09/no-original.png" alt="" />',
			'expected' => '<img src="https://example.org/wp-content/uploads/2019/09/no-original.webp" alt="" /><!-- Rocket has webp -->',
		],
	],
];
