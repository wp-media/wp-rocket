<?php

return [
	'shouldFilterLogoWhenDivi' => [
		'config'   => [
			'theme-name'     => 'Divi',
			'theme-template' => '',
			'images' => [
				'<img src="http://example.com/wp-content/uploads/logo.png" data-height-percentage="54">',
				'<img src="http://example.com/wp-content/uploads/my-picture.png">',
			],
		],
		'expected' => [
			'<img src="http://example.com/wp-content/uploads/my-picture.png">',
		],
	],

	'shouldFilterLogoWhenDiviChild' => [
		'config'   => [
			'theme-name'     => 'Divi Child',
			'theme-template' => 'divi',
			'images' => [
				'<img src="http://example.com/wp-content/uploads/logo.png" data-height-percentage="54">',
				'<img src="http://example.com/wp-content/uploads/my-picture.png">',
			],
		],
		'expected' => [
			'<img src="http://example.com/wp-content/uploads/my-picture.png">',
		],
	],

	'shouldFilterOnCaseInsensitiveAttribute' => [
		'config'   => [
			'theme-name'     => 'Divi',
			'theme-template' => '',
			'images' => [
				'<img src="http://example.com/wp-content/uploads/logo.png" DATA-height-PERcenTAGE="54">',
				'<img src="http://example.com/wp-content/uploads/my-picture.png">',
			],
		],
		'expected' => [
			'<img src="http://example.com/wp-content/uploads/my-picture.png">',
		],
	],

	'shouldNotFilterLogoWhenNotDivi' => [
		'config'   => [
			'theme-name'     => 'TwentyTwenty',
			'theme-template' => '',
			'images' => [
				'<img src="http://example.com/wp-content/uploads/logo.png" data-height-percentage="54">',
				'<img src="http://example.com/wp-content/uploads/my-picture.png">',
			],
		],
		'expected' => [
			'<img src="http://example.com/wp-content/uploads/logo.png" data-height-percentage="54">',
			'<img src="http://example.com/wp-content/uploads/my-picture.png">',
		],
	],
];
