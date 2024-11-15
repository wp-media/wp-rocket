<?php

return [
	'test_data' => [
		'testShouldReturnOriginalWhenNoGoogleFonts' => [
			'config' => [
				'html' => '<html><body></body></html>',
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'css_files' => []
			],
			'expected' => [
				'html' => '<html><body></body></html>'
			],
		],
		'testShouldRewriteV1Font' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v1.php' ),
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'css_files' => []
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1.php' ),
			],
		],
		'testShouldRewriteV2Font' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v2.php' ),
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'css_files' => []
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v2.php' ),
			],
		],
		'testShouldRewriteV1AndV2Fonts' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v1_v2.php' ),
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'css_files' => []
				],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1_v2.php' ),
			],
		],
		'testShouldRewriteFontV1PathInStyleTag' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v1.php' ),
				'host_fonts_locally' => true,
				'css_files' => [
					'wp-content/cache/fonts/google-fonts/1/e/b/c/173c0fc97eef86a6e51ada56c5a9a.css' => 'body { font-family: "Roboto"; }',
					'wp-content/cache/fonts/google-fonts/1/5/9/5/cb6ccb56826a802ed411cef875f0e.css' => 'body { font-family: "Open-San"; }',
				],
				'locally_inline_css' => true,
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1_style_tag.php' ),
			],
		]
	],

];
