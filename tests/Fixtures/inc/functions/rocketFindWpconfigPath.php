<?php
return [
	'vfs_dir' => 'public/',
	'structure' => [
		'wp-config.php' => 'test content here',
		'wp-config-alt.php' => 'test',
	],

	'test_data' => [
		'testShouldFindDefaultWpconfig' => [
			'config' => [
			],
			'expected' => 'vfs://public/wp-config.php',
		],
		'testShouldFindAnotherWpconfig' => [
			'config' => [
				'config_file_name' => 'wp-config-alt',

			],
			'expected' => 'vfs://public/wp-config-alt.php',
		],
	]
];
