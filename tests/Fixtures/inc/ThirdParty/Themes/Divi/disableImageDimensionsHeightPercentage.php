<?php

$original_html = <<<ORIGINALHTML
<!doctype>
<html>
<head></head>
<body>
	<img src="https://example.org/wp-content/uploads/logo.jpg" data-height-percentage="54">
	<img src="https://example.org/wp-content/uploads/my-picture.jpg">
</body>
</html>
ORIGINALHTML;

$expected_html = <<<EXPECTEDHTML
<!doctype>
<html>
<head></head>
<body>
	<img src="https://example.org/wp-content/uploads/logo.jpg" data-height-percentage="54">
	<img width="1" height="1" src="https://example.org/wp-content/uploads/my-picture.jpg">
</body>
</html>
EXPECTEDHTML;

return [
	'vfs_dir'   => '/',
	'structure' => [
		'wp-content' => [
			'uploads' => [
				'logo.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/ThirdParty/Themes/Divi/logo.jpg" ),
				'my-picture.jpg' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . "/inc/ThirdParty/Themes/Divi/my-picture.jpg" ),
			]
		]
	],

	'test_data' => [

		'shouldFilterLogoWhenDivi' => [
			'config'   => [
				'stylesheet'     => 'divi',
				'theme-name'     => 'Divi',
				'theme-template' => '',
				'is-child'       => '',
				'images'         => [
					'<img src="http://example.org/wp-content/uploads/logo.jpg" data-height-percentage="54">',
					'<img src="http://example.org/wp-content/uploads/my-picture.jpg">',
				],
			],
			'expected' => [
				'<img src="http://example.org/wp-content/uploads/my-picture.jpg">',
			],
			'html' => [
				'original' => $original_html,
				'expected' => $expected_html,
			]
		],

		'shouldFilterLogoWhenDiviChild' => [
			'config'   => [
				'stylesheet'     => 'child-of-divi',
				'theme-name'     => 'Divi Child',
				'theme-template' => 'divi',
				'is-child'       => 'divi',
				'parent-name'    => 'Divi',
				'images'         => [
					'<img src="http://example.org/wp-content/uploads/logo.jpg" data-height-percentage="54">',
					'<img src="http://example.org/wp-content/uploads/my-picture.jpg">',
				],
			],
			'expected' => [
				'<img src="http://example.org/wp-content/uploads/my-picture.jpg">',
			],
			'html' => [
				'original' => $original_html,
				'expected' => $expected_html,
			]
		],

		'shouldFilterOnCaseInsensitiveAttribute' => [
			'config'   => [
				'stylesheet'     => 'divi',
				'theme-name'     => 'Divi',
				'theme-template' => '',
				'is-child'       => '',
				'images'         => [
					'<img src="http://example.org/wp-content/uploads/logo.jpg" DATA-height-PERcenTAGE="54">',
					'<img src="http://example.org/wp-content/uploads/my-picture.jpg">',
				],
			],
			'expected' => [
				'<img src="http://example.org/wp-content/uploads/my-picture.jpg">',
			],
			'html' => [
				'original' => '<!doctype><html><body><img src="http://example.org/wp-content/uploads/logo.jpg" width="100" DATA-height-PERcenTAGE="54"></body></html>',
				'expected' => '<!doctype><html><body><img src="http://example.org/wp-content/uploads/logo.jpg" width="100" DATA-height-PERcenTAGE="54"></body></html>',
			],
		],
	],
];
