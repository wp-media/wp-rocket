<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [],
			],
		],
	],

	// Test data.
	'test_data' => [
		[
			'config'   => [
				'path' => 'public/wp-content/cache/wp-rocket/example.org',
			],
			'expected' => 'public/wp-content/cache/wp-rocket/example.org',
		],
		[
			'config'   => [
				'path'   => 'vfs://public/wp-content/cache/wp-rocket/',
				'escape' => true,
			],
			'expected' => 'vfs:\/\/public\/wp-content\/cache\/wp-rocket\/',
		],
		[
			'config'   => [
				'path'       => 'C:\public\wp-content\cache/wp-rocket/example.org',
				'is_windows' => true,
			],
			'expected' => 'C:\public\wp-content\cache\wp-rocket\example.org',
		],
		[
			'config'   => [
				'path'       => 'C:\public\wp-content\cache/wp-rocket/example.org/',
				'is_windows' => true,
				'escape'     => true,
			],
			'expected' => 'C:\\\\public\\\\wp-content\\\\cache\\\\wp-rocket\\\\example.org\\\\',
		],
	],
];
