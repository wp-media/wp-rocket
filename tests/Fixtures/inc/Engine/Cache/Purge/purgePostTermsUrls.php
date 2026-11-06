<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                => [
						'lorem-ipsum' => [
							'index.html'      => '',
						],
						'sit-amet' => [
							'index.html'      => '',
						],
						'semper-viverra' => [
							'index.html'      => '',
						],
						'nec-ullamcorper' => [
							'index.html'      => '',
						],
						'tag1' => [
							'index.html'      => '',
						],
						'tag2' => [
							'index.html'      => '',
						],
						'cat1' => [
							'index.html'      => '',
						],
						'cat2' => [
							'index.html'      => '',
						],
						'producttag' => [
							'index.html'      => '',
							'ups.html'        => '',
						],
					],
				],
			],
		],
	],
	'test_data' => [
		[
			'post_data' => [ 'post_title' => 'lorem-ipsum' ],
			'terms'     => [
				'post_tag' => [
					[ 'name' => 'tag1' ],
					[ 'name' => 'tag2' ],
				],
				'category' => [],
				'custom'   => [],
			],
			'expected'  => [
				'vfs://wp-content/cache/wp-rocket/example.org/tag1/index.html' => null,
				'vfs://wp-content/cache/wp-rocket/example.org/tag2/index.html' => null,
			],
		],

		[
			'post_data' => [ 'post_name' => 'sit-amet' ],
			'terms'     => [
				'post_tag' => [
					[ 'name' => 'tag1' ],
				],
				'category' => [
					[ 'slug' => 'cat1' ],
					[ 'slug' => 'cat2' ],
				],
				'custom'   => [],
			],
			'expected'  => [
				'vfs://wp-content/cache/wp-rocket/example.org/cat1/index.html' => null,
				'vfs://wp-content/cache/wp-rocket/example.org/cat2/index.html' => null,
			],
		],
		[
			'post_data' => [ 'post_name' => 'semper-viverra' ],
			'terms'     => [
				'post_tag' => [],
				'category' => [],
				'custom'   => [
					'tax'   => [
						'tax'         => 'tax1',
						'object_type' => 'post',
						'args'        => [],
					],
					'terms' => [
						[
							'name'     => 'tax1term1',
							'taxonomy' => 'tax1',
						],
					],
				],
			],
			'expected'  => [
				'vfs://wp-content/cache/wp-rocket/example.org/tax1/index.html'           => null,
				'vfs://wp-content/cache/wp-rocket/example.org/tax1/tax1term1/index.html' => null,
			],
		],

		// 'product_shipping_class' taxonomy.
		[
			'post_data' => [ 'post_name' => 'nec-ullamcorper' ],
			'terms'     => [
				'post_tag' => [
					[ 'name' => 'producttag' ],
				],
				'category' => [],
				'custom'   => [
					'tax'   => [
						'tax'         => 'product_shipping_class',
						'object_type' => 'post',
						'args'        => [],
					],
					'terms' => [
						[
							'name'     => 'first',
							'taxonomy' => 'product_shipping_class',
						],
					],
				],
			],
			'expected'  => [
				'vfs://wp-content/cache/wp-rocket/example.org/producttag/index.html' => null,
				'vfs://wp-content/cache/wp-rocket/example.org/producttag/ups.html' => null,
			],
		],
		// Non-public taxonomy.
		[
			'post_data' => [
				'post_title'   => 'nec-ullamcorper',
				'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
			],
			'terms'     => [
				'post_tag' => [
				],
				'category' => [],
				'custom'   => [
					'tax'   => [
						'tax'         => 'tax1',
						'object_type' => 'post',
						'args'        => [ 'public' => false ],
					],
					'terms' => [
						[
							'name'     => 'tax1term1',
							'taxonomy' => 'tax1',
						],
					],
				],
			],
			'expected'  => [],
		],

		[
			'post_data' => [ 'post_name' => 'abc' ],
			'terms'     => [
				'post_tag' => [],
				'category' => [
					[ 'slug' => 'cat1', 'parent' => 'cat2' ],
				],
				'custom'   => [],
			],

			'expected'  => [
				'vfs://wp-content/cache/wp-rocket/example.org/cat1/index.html' => null,
				'vfs://wp-content/cache/wp-rocket/example.org/cat2/index.html' => null,
			],
		],
		[
			'post_data' => [ 'post_name' => 'abc' ],
			'terms'     => [
				'post_tag' => [
					[ 'name' => 'tag1' ],
				],
				'category' => [
					[ 'slug' => 'cat1', 'parent' => 'cat2' ],
				],
				'custom'   => [],
			],

			'expected'  => [
				'vfs://wp-content/cache/wp-rocket/example.org/cat1/index.html' => null,
				'vfs://wp-content/cache/wp-rocket/example.org/cat2/index.html' => null,
				'vfs://wp-content/cache/wp-rocket/example.org/tag1/index.html' => null,
			],
		],
	],
];
