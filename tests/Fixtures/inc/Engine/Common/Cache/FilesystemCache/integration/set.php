<?php

$host_dir = 'wp-content/cache/background-css/1/example.org/';

return [
	'vfs_dir'   => 'wp-content/',
	'structure' => require __DIR__ . '/structure.php',
	'test_data' => [
		'shouldSaveInsideHostDir'                => [
			'config'   => [
				'key' => 'http://example.org/blog/file.css',
			],
			'expected' => [
				'output' => true,
				'path'   => $host_dir . 'blog/file.css',
				'absent' => [],
			],
		],
		'shouldClampParentSegmentsToHostDir'     => [
			'config'   => [
				'key' => 'http://example.org/a/../../../../../outside.css',
			],
			'expected' => [
				'output' => true,
				'path'   => $host_dir . 'outside.css',
				'absent' => [],
			],
		],
		'shouldClampBackslashSegmentsToHostDir'  => [
			'config'   => [
				'key' => 'http://example.org/a\..\..\..\..\..\other\keep.css',
			],
			'expected' => [
				'output' => true,
				'path'   => $host_dir . 'other/keep.css',
				'absent' => [],
			],
		],
		'shouldNotSaveWithParentHost'            => [
			'config'   => [
				'key' => 'http://../2/example.org/a.css',
			],
			'expected' => [
				'output' => false,
				'path'   => null,
				'absent' => [],
			],
		],
		'shouldNotSaveWithParentHostAndSegments' => [
			'config'   => [
				'key' => 'http://../../../outside.css',
			],
			'expected' => [
				'output' => false,
				'path'   => null,
				'absent' => [],
			],
		],
		'shouldNotSaveDisallowedExtension'       => [
			'config'   => [
				'key' => 'http://example.org/x.php',
			],
			'expected' => [
				'output' => false,
				'path'   => null,
				'absent' => [
					$host_dir . 'x.php',
				],
			],
		],
		'shouldNotSaveInnerExecutableExtension'  => [
			'config'   => [
				'key' => 'http://example.org/x.php.css',
			],
			'expected' => [
				'output' => false,
				'path'   => null,
				'absent' => [
					$host_dir . 'x.php.css',
				],
			],
		],
	],
];
