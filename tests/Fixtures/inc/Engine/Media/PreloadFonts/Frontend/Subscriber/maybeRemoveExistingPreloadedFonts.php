<?php

return [
	'shouldRemoveExistingPreloadedFonts' => [
		'config' => [
			'remove_existing_preloaded_fonts' => true,
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
		],
		'expected' => [
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200_output.php' ),
		]
	],
	'shouldNotRemoveExistingPreloadedFonts' => [
		'config' => [
			'remove_existing_preloaded_fonts' => false,
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
		],
		'expected' => [
			'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Media/PreloadFonts/Frontend/Subscriber/HTML/preloadfonts_customDifferentTill1200.php' ),
		]
	],
];
