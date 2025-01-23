<?php

return [
	'test_data' => [
		'testShouldReturnOriginalWhenNoGoogleFonts' => [
			'config' => [
				'html' => '<html><body></body></html>',
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
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
				'http' => [
					'https://fonts.googleapis.com/css?family=Roboto' => [
						'body' => 'body { font-family: "Roboto"; }',
						'response' => ['code' => 200 ]
					],
					'https://fonts.googleapis.com/css?family=Open+Sans' => [
						'body' => 'body { font-family: "Open-San"; }',
						'response' => ['code' => 200 ]
					],
				],
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
				'http' => [
					'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' => [
						'body' => 'body { font-family: "Roboto"; }',
						'response' => ['code' => 200 ]
					],
					'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap' => [
						'body' => 'body { font-family: "Lato"; }',
						'response' => ['code' => 200 ]
					],
				],
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
				'http' => [
					'https://fonts.googleapis.com/css?family=Roboto|Open+Sans' => [
						'body' => '.roboto { font-family: "Roboto"; } .open-san { font-family: "Open-San"; }',
						'response' => ['code' => 200 ]
					],
					'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@400;700&display=swap' => [
						'body' => '.lato { font-family: "Lato"; } .montserrat { font-family: "Montserrat"; }',
						'response' => ['code' => 200 ]
					],
				],
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1_v2.php' ),
			],
		],
		'testShouldRewriteV1AndExcludeV2Fonts' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v1_v2.php' ),
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'http' => [
					'https://fonts.googleapis.com/css?family=Roboto|Open+Sans' => [
						'body' => '.roboto { font-family: "Roboto"; } .open-san { font-family: "Open-San"; }',
						'response' => ['code' => 200 ]
					],
					'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@400;700&display=swap' => [
						'body' => '.lato { font-family: "Lato"; } .montserrat { font-family: "Montserrat"; }',
						'response' => ['code' => 200 ]
					],
				],
				'exclude_locally_host_fonts' => [
					'Lato',
				]
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1_excluded_v2.php' ),
			],
		],
		'testShouldRewriteFontV1PathInStyleTag' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v1.php' ),
				'host_fonts_locally' => true,
				'http' => [
					'https://fonts.googleapis.com/css?family=Roboto' => [
						'body' => 'body { font-family: "Roboto"; }',
						'response' => ['code' => 200 ]
					],
					'https://fonts.googleapis.com/css?family=Open+Sans' => [
						'body' => 'body { font-family: "Open-San"; }',
						'response' => ['code' => 200 ]
					],
				],
				'locally_inline_css' => true,
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1_style_tag.php' ),
			],
		],
		'testShouldExcludeFont' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v2_from_combination.php' ),
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'http' => [
					'https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=MontSerra:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=optional' => [
						'body' => '',
						'response' => ['code' => 200 ]
					],
				],
				'exclude_locally_host_fonts' => [
					'Lato',
				]
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v2_from_combination.php' ),
			],
		],
		'testShouldExcludeFontWithRegex' => [
			'config' => [
				'html' => file_get_contents( __DIR__ . '/HTML/input_v1_v2_regex.php' ),
				'host_fonts_locally' => true,
				'locally_inline_css' => false,
				'http' => [
					'https://fonts.googleapis.com/css?family=Roboto|Open+Sans' => [
						'body' => '.roboto { font-family: "Roboto"; } .open-san { font-family: "Open-San"; }',
						'response' => ['code' => 200 ]
					],
					'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@400;700&display=swap' => [
						'body' => '.lato { font-family: "Lato"; } .montserrat { font-family: "Montserrat"; }',
						'response' => ['code' => 200 ]
					],
				],
				'exclude_locally_host_fonts' => [
					'family=Rob(.*)o',
					'family(.*)Montserrat(.*)display=swap',
					'astra-google-(.*)-css',
				]
			],
			'expected' => [
				'html' => file_get_contents( __DIR__ . '/HTML/expected_v1_v2_regex.php' ),
			],
		],
	]
];
