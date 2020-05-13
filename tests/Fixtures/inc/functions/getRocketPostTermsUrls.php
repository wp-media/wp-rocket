<?php

return [
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
			'http://example.org/?cat=1',
			'http://example.org/?tag=tag1',
			'http://example.org/?tag=tag2',
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
			'http://example.org/?cat=cat1', // cat1 gets replaced by its ID.
			'http://example.org/?cat=cat2', // cat2 gets replaced by its ID.
			'http://example.org/?tag=tag1',
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
			'http://example.org/?cat=1',
			'http://example.org/tax1/tax1term1/',
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
			'http://example.org/?cat=1',
			'http://example.org/?tag=producttag',
		],
	],
	// Non-public taxonomy.
	[
		'post_data' => [
			'post_title'   => 'Nec ullamcorper',
			'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
		],
		'terms'     => [
			'post_tag' => [],
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
		'expected'  => [
			'http://example.org/?cat=1',
		],
	],
];
