<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [

		'shouldFilterLogoWhenDivi' => [
			'config'   => [
				'stylesheet'     => 'divi',
				'theme-name'     => 'Divi',
				'theme-template' => '',
				'is-child'       => '',
				'images'         => [
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
				'stylesheet'     => 'child-of-divi',
				'theme-name'     => 'Divi Child',
				'theme-template' => 'divi',
				'is-child'       => 'divi',
				'parent-name'    => 'Divi',
				'images'         => [
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
				'stylesheet'     => 'divi',
				'theme-name'     => 'Divi',
				'theme-template' => '',
				'is-child'       => '',
				'images'         => [
					'<img src="http://example.com/wp-content/uploads/logo.png" DATA-height-PERcenTAGE="54">',
					'<img src="http://example.com/wp-content/uploads/my-picture.png">',
				],
			],
			'expected' => [
				'<img src="http://example.com/wp-content/uploads/my-picture.png">',
			],
		],
	],
];
