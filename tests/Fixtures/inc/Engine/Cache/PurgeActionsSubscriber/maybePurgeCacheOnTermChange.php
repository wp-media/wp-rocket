<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                                => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
			],
		],
	],
	'test_data' => [
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