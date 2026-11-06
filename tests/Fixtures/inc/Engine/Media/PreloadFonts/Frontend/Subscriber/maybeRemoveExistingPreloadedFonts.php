<?php

return [
	'shouldNotRemoveExistingPreloadedFontsWhenFirstVisit' => [
		'config' => [
			'remove_existing_preloaded_fonts' => true,
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
			'row' => [],
		],
		'expected' => [
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
		]
	],
	'shouldRemoveExistingPreloadedFonts' => [
		'config' => [
			'remove_existing_preloaded_fonts' => true,
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'fonts' => json_encode( [
					'path/to/font1.woff2',
				] ),
			],
		],
		'expected' => [
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200_output.php' ),
		]
	],
	'shouldNotRemoveExistingPreloadedFontsWhenFilterFalse' => [
		'config' => [
			'remove_existing_preloaded_fonts' => false,
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
			'row' => [],
		],
		'expected' => [
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
		]
	],
];
