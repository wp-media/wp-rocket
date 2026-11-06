<?php

$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Addon/WebP/Subscriber/original.html' );
$updated = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Addon/WebP/Subscriber/updated.html' );
$nomatch = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Addon/WebP/Subscriber/nomatch.html' );

return [
	'vfs_dir' => 'wp-content/',
	'structure' => [
		'wp-content' => [
			'uploads' => [
				'2019' => [
					'09' => [
						'one-image.png' => '',
						'one-image.webp' => '',
						'one-image-60x60.png' => '',
					],
				],
				'2017' => [
					'02' => [
						'apple-touch-icon.png' => '',
						'apple-touch-icon.png.webp' => '',
						'favicon-32x32.png' => '',
						'favicon-32x32.webp' => '',
						'mstile-144x144.png' => '',
						'mstile-144x144.webp' => '',
						'stats-php.gif' => '',
						'stats-php.gif.webp' => '',
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
		'shouldConvertResponsivePreloadWithDefaultAttributes' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
			'expected' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.webp" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.webp 600w,http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.webp" srcset="http://example.org/wp-content/uploads/2019/09/one-image.webp 600w,http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px"><!-- Rocket has webp -->',
		],
		'shouldConvertResponsivePreloadWithoutHref' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png?v=1 1x, http://example.org/wp-content/uploads/2017/02/apple-touch-icon.png?v=2 2x">',
			'expected' => '<link rel="preload" as="image" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.webp?v=1 1x,http://example.org/wp-content/uploads/2017/02/apple-touch-icon.png.webp?v=2 2x"><!-- Rocket has webp -->',
		],
		'shouldPreserveResponsivePreloadWhenWebpIsMissing' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w">',
			'expected' => '<link rel="preload" as="image" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w"><!-- Rocket no webp -->',
		],
		'shouldConvertCdnResponsivePreload' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" imagesrcset="http://cdn-example.net/wp-content/uploads/2019/09/one-image.png 600w">',
			'expected' => '<link rel="preload" as="image" imagesrcset="http://cdn-example.net/wp-content/uploads/2019/09/one-image.webp 600w"><!-- Rocket has webp -->',
		],
		'shouldPreserveExternalResponsivePreload' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" imagesrcset="http://external.example/wp-content/uploads/2019/09/one-image.png 600w">',
			'expected' => '<link rel="preload" as="image" imagesrcset="http://external.example/wp-content/uploads/2019/09/one-image.png 600w"><!-- Rocket no webp -->',
		],
		'shouldPreserveResponsivePreloadWhenCacheDisabled' => [
			'config' => [
				'webp' => 0,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
			'expected' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
		],
		'shouldPreserveResponsivePreloadWhenDisabledByFilter' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => true,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
			'expected' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
		],
		'shouldPreserveResponsivePreloadWithoutWebpSupport' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => '*/*',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => null,
			],
			'original' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
			'expected' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
		],
		'shouldRespectCustomAttributesForResponsivePreload' => [
			'config' => [
				'webp' => 1,
				'filter_disable' => false,
				'headers' => 'image/webp',
				'filter_ext' => [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ],
				'filter_attr' => [ 'href', 'src', 'srcset' ],
			],
			'original' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.png" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.png" srcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px">',
			'expected' => '<link rel="preload" as="image" href="http://example.org/wp-content/uploads/2019/09/one-image.webp" imagesrcset="http://example.org/wp-content/uploads/2019/09/one-image.png 600w, http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" imagesizes="(max-width: 600px) 100vw, 600px"><img src="http://example.org/wp-content/uploads/2019/09/one-image.webp" srcset="http://example.org/wp-content/uploads/2019/09/one-image.webp 600w,http://example.org/wp-content/uploads/2019/09/one-image-60x60.png 60w" sizes="(max-width: 600px) 100vw, 600px"><!-- Rocket has webp -->',
		],
	],
];
