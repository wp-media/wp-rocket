<?php

return [
	'vfs_dir'   => 'wp-content/cache/min/',

	// Test data.
	'test_data' => [
		[
			'target'       => 'wp-content/cache/min/1/',
			'should_mkdir' => false,
		],
		[
			'target'       => 'wp-content/cache/wp-rocket/example.org/about/',
			'should_mkdir' => true,
		],
		[
			'target'       => 'wp-content/cache/wp-rocket/example.org/parent1/child1/grandchild1',
			'should_mkdir' => true,
		],
		[
			'target'       => '/',
			'should_mkdir' => false,
		],
		[
			'target'       => '/test1',
			'should_mkdir' => true,
		],

		// Double //.
		[
			'target'       => 'wp-content//cache//wp-rocket//example.org/parent1//child1//grandchild1//greatgrandchild1',
			'should_mkdir' => true,
			'new_path'     => 'vfs://public/wp-content/cache/wp-rocket/example.org/parent1/child1/grandchild1/greatgrandchild1/',
		],

		// Try with non-stream URLs.
		[
			'target'       => 'bitcoin://example.org/2020/03/',
			'should_mkdir' => true,
			'new_path'     => 'vfs://public/bitcoin:/example.org/2020/03/',
		],
		[
			'target'       => 'content://example.org/wp-content/cache/',
			'should_mkdir' => true,
			'new_path'     => 'vfs://public/content:/example.org/wp-content/cache/',
		],
	],
];
