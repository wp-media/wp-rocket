<?php
return [
	'vfs_dir' => 'public/wordpress/',
	'structure' => [
		'wordpress' => [
			'wp-config.php' => 'test content here',
			'wp-config-changename.php' => 'test',
		],
		'wp-config-alt.php' => 'Outside config file contents',
	],

	'test_data' => [
		'testShouldFindDefaultWpconfig' => [
			'config' => [
			],
			'expected' => 'vfs://public/wordpress/wp-config.php',
		],
		'testShouldFindAnotherWpconfig' => [
			'config' => [
				'config_file_name' => 'wp-config-changename',
			],
			'expected' => 'vfs://public/wordpress/wp-config-changename.php',
		],
		'testShouldFindAlternativeWpconfig' => [
			'config' => [
				'config_file_name' => 'wp-config-alt',
			],
			'expected' => 'vfs://public/wp-config-alt.php',
		],
		'testShouldBailOutWpconfig' => [
			'config' => [
				'config_file_name' => 'wp-config-notfound',
			],
			'expected' => false,
		],
	]
];
