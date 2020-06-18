<?php
return [
	'vfs_dir' => '/wp-content/wp-rocket-config',

	'structure' => [
		'wp_content' => [
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
			[
				'example.org.php'
			]
		],

		'shouldHandleEmptyDirectory' => [
			[],
		],

		'shouldHandleDirWithAssortedFiletypes' => [
			[
				'somefile.php',
				'another-file.php',
				'count-thisfile-please.php',
				'yet-anothercountable.file.php'
			]
		]
	]
];
