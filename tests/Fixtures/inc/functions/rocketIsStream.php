<?php

return [
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		[
			'target'       => 'vfs://public/wp-content/cache/min/1/',
			'expected' => true,
		],
		[
			'target'       => 'vfs://public/wp-content/cache/wp-rocket/example.org/about/',
			'expected' => true,
		],
		[
			'target'       => 'http://example.org/about/',
			'expected' => true,
		],
		[
			'target'       => 'https://example.org/about/',
			'expected' => true,
		],

		// Not a stream.
		[
			'target'       => 'wp-content/cache/wp-rocket/example.org/parent1/child1/grandchild1',
			'expected' => false,
		],
		[
			'target'       => '/',
			'expected' => false,
		],
		[
			'target'       => '//example.org',
			'expected' => false,
		],

		// Try with non-stream URLs.
		[
			'target'       => 'bitcoin://example.org/2020/03/',
			'expected' => false,
		],
		[
			'target'       => 'content://example.org/wp-content/cache/',
			'expected' => false,
		],
	],
];
