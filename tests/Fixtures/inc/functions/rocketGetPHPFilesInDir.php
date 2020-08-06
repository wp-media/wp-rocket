<?php
return [
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content' => [
			'wp-rocket-config' => [
				'.'               => '',
				'..'              => '',
				'example.org.php' => '',
			],
			'empty'            => [
				'.'  => '',
				'..' => ''
			],
			'assorted'         => [
				'.'                              => '',
				'..'                             => '',
				'somefile.php'                   => '',
				'another-file.php'               => '',
				'scriptfile.js'                  => '',
				'xmlfile.xml'                    => '',
				'jsonfile.json'                  => '',
				'count-thisfile-please.php'      => '',
				'donotcountthisfile.phpx'        => '',
				'yet-anothercountable.file.php'  => '',
				'whatkindoffileisthis.php.crazy' => ''
			],
		]
	],

	'test_data' => [
		'shouldHandleWPRocketConfigContents' => [
			'dir'      => 'vfs://public/wp-content/wp-rocket-config/',
			'expected' => [
				'example.org.php'
			]
		],

		'shouldHandleEmptyDirectory' => [
			'dir'      => 'vfs://public/wp-content/empty',
			'expected' => [],
		],

		'shouldHandleDirWithAssortedFiletypes' => [
			'dir'      => 'vfs://public/wp-content/assorted',
			'expected' => [
				'somefile.php',
				'another-file.php',
				'count-thisfile-please.php',
				'yet-anothercountable.file.php'
			]
		],

		'shouldHandleBadDir' => [
			'dir'      => 'vfs://public/wp-content/road/to/nowhere/',
			'expected' => []
		]
	]
];
