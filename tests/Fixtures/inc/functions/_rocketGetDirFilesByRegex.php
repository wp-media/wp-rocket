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
				'.'                                   => '',
				'..'                                  => '',
				'somefile.php'                        => '',
				'another-file.php'                    => '',
				'scriptfile.js'                       => '',
				'xmlfile.xml'                         => '',
				'jsongoogle.json'                     => '',
				'googleTagManager.js'                 => '',
				'anotherFileFromGoogle'               => '',
				'count-thisfile-please.php'           => '',
				'donotcountthisfile.phpx'             => '',
				'yet-anothercountable.file.php'       => '',
				'whatkindofcrazyfileisthis.php.crazy' => '',
				'critical.php.html'                   => '',
				'template.html.php'                   => ''
			],
			'regex'            => [
				'Google.php'          => '',
				'googleTagManager.js' => '',
				'analytics.google.js' => '',
				'google-analytics.js' => '',
				'tagmanager'          => '',
			],
			'exact-match'      => [
				'filename.matchme.php' => '',
				'filename.gotcha.php'  => '',
			],
		],
	],

	'test_data' => [
		'shouldHandleWPRocketConfigContents' => [
			'dir'      => 'vfs://public/wp-content/wp-rocket-config/',
			'regex'    => '/.php$/',
			'expected' => [
				'example.org.php'
			]
		],

		'shouldHandleEmptyDirectory' => [
			'dir'      => 'vfs://public/wp-content/empty',
			'regex'    => '/.js$/',
			'expected' => [],
		],

		'shouldHandleExactMatch' => [
			'dir'      => 'vfs://public/wp-content/exact-match',
			'regex'    => '/filename.matchme.php/',
			'expected' => [
				'filename.matchme.php',
			]
		],

		'shouldHandleDirWithAssortedFiletypes' => [
			'dir'      => 'vfs://public/wp-content/assorted',
			'regex'    => '/.php$/',
			'expected' => [
				'somefile.php',
				'another-file.php',
				'count-thisfile-please.php',
				'yet-anothercountable.file.php',
				'template.html.php'
			]
		],

		'shouldHandleGetAllFiles' => [
			'dir'      => 'vfs://public/wp-content/assorted',
			'regex'    => '/.*/',
			'expected' => [
				'somefile.php',
				'another-file.php',
				'scriptfile.js',
				'xmlfile.xml',
				'jsongoogle.json',
				'googleTagManager.js',
				'anotherFileFromGoogle',
				'count-thisfile-please.php',
				'donotcountthisfile.phpx',
				'yet-anothercountable.file.php',
				'whatkindofcrazyfileisthis.php.crazy',
				'critical.php.html',
				'template.html.php',
			],
		],

		'shouldHandleBadDir' => [
			'dir'      => 'vfs://public/wp-content/road/to/nowhere/',
			'regex'    => '/^google/',
			'expected' => []
		]
	]
];
