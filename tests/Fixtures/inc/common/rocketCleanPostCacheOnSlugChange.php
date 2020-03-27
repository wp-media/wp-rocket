<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/example.org/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html'         => '',
						'index.html_gzip'    => '',
						'lorem-ipsum'        => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
						'nec-ullamcorper'    => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
						'enim-nunc-faucibus' => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
					],
				],
			],
		],
	],

	'posts'     => [
		'lorem-ipsum'        => [
			'post_title'   => 'Lorem ipsum',
			'post_content' => 'Lorem ipsum dolor sit amet',
			'post_excerpt' => 'Lorem ipsum dolor sit amet',
			'post_status'  => 'publish',
		],
		'nec-ullamcorper'    => [
			'post_title'   => 'Nec ullamcorper',
			'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
			'post_excerpt' => 'Nec ullamcorper sit amet risus nullam eget.',
			'post_status'  => 'publish',
		],
		'enim-nunc-faucibus' => [
			'post_title'   => 'Enim nunc faucibus',
			'post_content' => 'Enim nunc faucibus a pellentesque sit amet porttitor eget.',
			'post_excerpt' => 'Enim nunc faucibus a pellentesque sit amet porttitor eget.',
			'post_status'  => 'publish',
		],
	],

	// Test data.
	'test_data' => [
		// Test should bail out when the post status is 'draft', 'pending', or 'auto-draft'.
		[
			'lorem-ipsum',
			[
				'post_status' => 'draft',
			],
		],
		[
			'nec-ullamcorper',
			[
				'post_status' => 'pending',
			],
		],
		[
			'enim-nunc-faucibus',
			[
				'post_status' => 'auto-draft',
			],
		],

		// Test should bail out when the slug (post_name) didn't change.
		[
			'lorem-ipsum',
			[
				'post_content' => '[Updated] Lorem ipsum dolor sit amet',
			],
		],
		[
			'nec-ullamcorper',
			[
				'post_content' => '[Updated] Nec ullamcorper sit amet risus nullam eget.',
			],
		],
		[
			'enim-nunc-faucibus',
			[
				'post_content' => '[Updated] Enim nunc faucibus a pellentesque sit amet porttitor eget.',
			],
		],

		// Test should fire rocket_clean_files() when slug (post_name) does change.
		[
			'lorem-ipsum',
			[
				'post_name'   => 'updated-lorem-ipsum',
				'post_title'  => '[Updated] Lorem ipsum',
			],
		],
		[
			'nec-ullamcorper',
			[
				'post_name'   => 'updated-nec-ullamcorper',
				'post_title'  => '[Updated] Nec ullamcorper',
			],
		],
		[
			'enim-nunc-faucibus',
			[
				'post_name'   => 'updated-enim-nunc-faucibus',
				'post_title'  => '[Updated] Enim nunc faucibus',
			],
		],
	],
];
