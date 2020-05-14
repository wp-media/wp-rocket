<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',
	'cleaned'   => [
		'vfs://public/wp-content/cache/wp-rocket/example.org' => null,
		'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
		'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654' => null,
	],
	'test_data' => [
		'testCreateTerm' => [
			'action' => 'create_term',
		],
		'testUpdateTerm' => [
			'action' => 'edit_term',
		],
		'testDeleteTerm' => [
			'action' => 'delete_term',
		],
	],
	'unit_test_data' => [
		'testTaxonomyReturnFalse' => [
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
		'testTaxonomyNotPublic' => [
			'name'     => 'category',
			'taxonomy' => (object) [
				'public'             => false,
				'publicly_queryable' => true,
			],
			'clean'    => false,
		],
		'testTaxonomyNotPubliclyQueryable' => [
			'name'     => 'category',
			'taxonomy' => (object) [
				'public'             => true,
				'publicly_queryable' => false,
			],
			'clean'    => false,
		],
		'testTaxonomyPublic' => [
			'name'     => 'category',
			'taxonomy' => (object) [
				'public'             => true,
				'publicly_queryable' => true,
			],
			'clean'    => true,
		],
	],
];