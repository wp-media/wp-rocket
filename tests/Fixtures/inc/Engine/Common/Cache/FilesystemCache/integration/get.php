<?php

return [
	'vfs_dir'   => 'wp-content/',
	'structure' => require __DIR__ . '/structure.php',
	'test_data' => [
		'shouldReadInsideHostDir'               => [
			'config'   => [
				'key' => 'http://example.org/a.css',
			],
			'expected' => 'a',
		],
		'shouldClampParentSegmentsToHostDir'    => [
			'config'   => [
				'key' => 'http://example.org/a/../../../../../outside.css',
			],
			'expected' => 'default',
		],
		'shouldClampBackslashSegmentsToHostDir' => [
			'config'   => [
				'key' => 'http://example.org/a\..\..\..\..\..\other\keep.css',
			],
			'expected' => 'default',
		],
		'shouldNotReadWithParentHost'           => [
			'config'   => [
				'key' => 'http://../2/example.org/a.css',
			],
			'expected' => 'default',
		],
		'shouldNotReadWithParentHostAndSegments' => [
			'config'   => [
				'key' => 'http://../../../outside.css',
			],
			'expected' => 'default',
		],
	],
];
