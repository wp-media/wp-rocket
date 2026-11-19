<?php

$host_dir = 'wp-content/cache/background-css/1/example.org/';

return [
	'vfs_dir'   => 'wp-content/',
	'structure' => require __DIR__ . '/structure.php',
	'test_data' => [
		'shouldDeleteInsideHostDir'                => [
			'config'   => [
				'key' => 'http://example.org/a.css',
			],
			'expected' => [
				'output'  => true,
				'deleted' => [
					$host_dir . 'a.css',
				],
			],
		],
		'shouldClampParentSegmentsToHostDir'       => [
			'config'   => [
				'key' => 'http://example.org/a/../../../../../outside.css',
			],
			'expected' => [
				'output'  => false,
				'deleted' => [],
			],
		],
		'shouldClampBackslashSegmentsToHostDir'    => [
			'config'   => [
				'key' => 'http://example.org/a\..\..\..\..\..\other\keep.css',
			],
			'expected' => [
				'output'  => false,
				'deleted' => [],
			],
		],
		'shouldNotDeleteWithParentHost'            => [
			'config'   => [
				'key' => 'http://../2/example.org/a.css',
			],
			'expected' => [
				'output'  => false,
				'deleted' => [],
			],
		],
		'shouldNotDeleteOtherBlogDir'              => [
			'config'   => [
				'key' => 'http://../2/',
			],
			'expected' => [
				'output'  => false,
				'deleted' => [],
			],
		],
		'shouldNotDeleteHostDir'                   => [
			'config'   => [
				'key' => 'http://example.org/',
			],
			'expected' => [
				'output'  => false,
				'deleted' => [],
			],
		],
	],
];
