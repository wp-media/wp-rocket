<?php
/**
 * Data Provider for _rocketGetDirectoryPHPFilesArray() tests.
 *
 * @author  Caspar Green
 * @since   ver 3.6.1
 */

return [
	'vfs_dir' => 'wp-content/wp-rocket-config/',

	'test_data' => [
		// Simulates expected contents of WP_ROCKET_CONFIG_PATH
		[
			[
				'.',
				'..',
				'example.org.php'
			],
			[
				'example.org.php'
			]
		],

		// Empty Directory
		[
			[
				'.',
				'..'
			],
			[],
		],

		// Directory with assorted file types
		[
			[
				'.',
				'..',
				'somefile.php',
				'another-file.php',
				'scriptfile.js',
				'xmlfile.xml',
				'jsonfile.json',
				'count-thisfile-please.php',
				'donotcountthisfile.phpx',
				'yet-anothercountable.file.php',
				'whatkindoffileisthis.php.crazy'
			],
			[
				'somefile.php',
				'another-file.php',
				'count-thisfile-please.php',
				'yet-anothercountable.file.php'
			]
		]
	]
];
