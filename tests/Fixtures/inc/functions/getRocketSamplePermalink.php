<?php

return [

	[
		[
			'structure'           => '/%postname%/',
			'override_post_title' => null,
			'override_post_name'  => null,
		],
		[
			'post_title'   => 'Lorem ipsum',
			'post_content' => 'Lorem ipsum dolor sit amet',
		],
		[
			'http://example.org/lorem-ipsum/',
			'lorem-ipsum',
		],
	],
	[
		[
			'structure'           => '/%year%/%postname%/',
			'override_post_title' => null,
			'override_post_name'  => null,
		],
		[
			'post_title'   => 'Nec ullamcorper',
			'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
			'post_date'    => '2019-10-24',
		],
		[
			'http://example.org/2019/nec-ullamcorper/',
			'nec-ullamcorper',
		],
	],
	[
		[
			'structure'           => '/%year%/%monthnum%/%postname%/',
			'override_post_title' => null,
			'override_post_name'  => null,
		],
		[
			'post_title'   => 'Enim nunc faucibus',
			'post_content' => 'Enim nunc faucibus a pellentesque sit amet porttitor eget.',
			'post_status'  => 'draft',
			'post_date'    => '2012-05-13',
		],
		[
			'http://example.org/2012/05/enim-nunc-faucibus/',
			'enim-nunc-faucibus',
		],
	],

	[
		[
			'structure'           => '/%year%/%monthnum%/%postname%/',
			'override_post_title' => '[UPDATED] Enim nunc faucibus',
			'override_post_name'  => '',
		],
		[
			'post_title'   => 'Enim nunc faucibus',
			'post_content' => 'Enim nunc faucibus a pellentesque sit amet porttitor eget.',
			'post_status'  => 'draft',
			'post_date'    => '2012-05-13',
		],
		[
			'http://example.org/2012/05/updated-enim-nunc-faucibus/',
			'updated-enim-nunc-faucibus',
		],
	],
	[
		[
			'structure'           => '/%year%/%postname%/',
			'override_post_title' => null,
			'override_post_name'  => 'override-nec-ullamcorper',
		],
		[
			'post_title'   => 'Nec ullamcorper',
			'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
			'post_date'    => '2019-10-24',
		],
		[
			'http://example.org/2019/override-nec-ullamcorper/',
			'override-nec-ullamcorper',
		],
	],

	// Test page hierarchy.
	[
		[
			'structure'           => '/%year%/%postname%/',
			'override_post_title' => null,
			'override_post_name'  => null,
			'parent_post'         => [
				'post_title'   => 'Dolor sit amet',
				'post_content' => 'Dolor sit amet consectetur adipiscing.',
				'post_date'    => '2019-10-24',
				'post_type'    => 'page',
			],
		],
		[
			'post_title'   => 'Odio eu feugiat',
			'post_content' => 'Odio eu feugiat pretium nibh. Dolor sit amet consectetur adipiscing.',
			'post_date'    => '2019-10-24',
			'post_type'    => 'page',
		],
		[
			'http://example.org/dolor-sit-amet/odio-eu-feugiat/',
			'odio-eu-feugiat',
		],
	],
];
