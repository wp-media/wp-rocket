<?php

$cleaned = [
	'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
];

return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'test_data' => [

		'testShouldNotPurgeCacheWhenNotPublic_create_term' => [
			'action'   => 'create_term',
			'public'   => false,
			'expected' => [
				'cleaned' => [],
			],
		],
		'testShouldNotPurgeCacheWhenNotPublic_edit_term' => [
			'action'   => 'edit_term',
			'public'   => false,
			'expected' => [
				'cleaned' => [],
			],
		],
		'testShouldNotPurgeCacheWhenNotPublic_delete_term' => [
			'action'   => 'delete_term',
			'public'   => false,
			'expected' => [
				'cleaned' => [],
			],
		],

		'testShouldPurgeCacheWhenPublic_create_term' => [
			'action'   => 'create_term',
			'public'   => true,
			'expected' => [
				'cleaned' => $cleaned,
			],
		],
		'testShouldPurgeCacheWhenPublic_edit_term'   => [
			'action'   => 'edit_term',
			'public'   => true,
			'expected' => [
				'cleaned' => $cleaned,
			],
		],
		'testShouldPurgeCacheWhenPublic_delete_term' => [
			'action'   => 'delete_term',
			'public'   => true,
			'expected' => [
				'cleaned' => $cleaned,
			],
		],
	],

	'unit_test_data' => [
		'testTaxonomyReturnFalse'                      => [
			'name'     => 'foo',
			'taxonomy' => false,
			'clean'    => false,
		],
		'testTaxonomyNotPublicAndNotPubliclyQueryable' => [
			'name'     => 'not_public',
			'taxonomy' => (object) [
				'public'             => false,
				'publicly_queryable' => false,
			],
			'clean'    => false,
		],
		'testTaxonomyNotPublic'                        => [
			'name'     => 'category',
			'taxonomy' => (object) [
				'public'             => false,
				'publicly_queryable' => true,
			],
			'clean'    => false,
		],
		'testTaxonomyNotPubliclyQueryable'             => [
			'name'     => 'category',
			'taxonomy' => (object) [
				'public'             => true,
				'publicly_queryable' => false,
			],
			'clean'    => false,
		],
		'testTaxonomyPublic'                           => [
			'name'     => 'category',
			'taxonomy' => (object) [
				'public'             => true,
				'publicly_queryable' => true,
			],
			'clean'    => true,
		],
	],
];
