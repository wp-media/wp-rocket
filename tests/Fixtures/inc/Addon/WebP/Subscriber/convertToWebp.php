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
	],
];
