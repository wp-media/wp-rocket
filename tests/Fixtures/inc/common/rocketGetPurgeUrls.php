<?php

return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'test_purge.html' => '',
					'folder_1' => [
						'test_purge.html' => ''
					]
				]
			]
		]
	],

	'urls' => [
		'posts' => [
			2 => 'http://www.example.org/next_post',
			3 => 'http://www.example.org/test_parent',
			4 => 'http://www.example.org/test_parent_2',
			5 => 'http://www.example.org/test2',
			6 => 'http://www.example.org/test3',
			20 => 'http://www.example.org/blog'
		],
		'authors' => [
			1 => 'http://www.example.org/author/author_name'
		],
		'archives' => [
			'page' => false,
			'post' => 'http://www.example.org/',
			'custompost' => 'http://www.example.org/custompost/',
		]
	],

	'test_data' => [
		'shouldReturnPostUrls' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'cache_purge_pages' => false
				],

				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://www.example.org/test',
				'http://www.example.org/blog',
				'http://www.example.org/next_post',
				'http://www.example.org/author/author_name',
				'http://www.example.org/test_parent',
				'http://www.example.org/test_parent_2'
			]
		],

		'shouldReturnPageUrls' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'cache_purge_pages' => false
				],
				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test',
					'post_type' => 'page',
					'next_post_id' => 2,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				],

			],
			'expected' => [
				'http://www.example.org/test',
				'http://www.example.org/next_post',
				'http://www.example.org/author/author_name',
				'http://www.example.org/test_parent',
				'http://www.example.org/test_parent_2'
			]
		],

		'shouldReturnCustomPostUrls' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'cache_purge_pages' => false
				],
				'post_data' => [
					'ID' => 1,
					'post_name' => 'test_custom_post',
					'url' => '/test_custom_post',
					'post_type' => 'custompost',
					'next_post_id' => 2,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				],

				'is_ssl' => false,
			],
			'expected' => [
				'http://www.example.org/test_custom_post',
				'http://www.example.org/custompost/index.html',
				'http://www.example.org/custompost/index.html_gzip',
				'http://www.example.org/custompost/indexpage',
				'http://www.example.org/next_post',
				'http://www.example.org/author/author_name',
				'http://www.example.org/test_parent',
				'http://www.example.org/test_parent_2'
			]
		],

		'shouldReturnPostUrlsWithPreviousPost' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'cache_purge_pages' => false
				],

				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test',
					'post_type' => 'post',
					'next_post_id' => 2,
					'prev_post_id' => 5,
					'prev_in_term_post_id' => 6,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://www.example.org/test',
				'http://www.example.org/blog',
				'http://www.example.org/next_post',
				'http://www.example.org/test2',
				'http://www.example.org/test3',
				'http://www.example.org/author/author_name',
				'http://www.example.org/test_parent',
				'http://www.example.org/test_parent_2'
			]
		],

		'shouldReturnPostUrlsWithPurgeUrls' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'home' => 'http://www.example.org/',
					'cache_purge_pages' => [
						'(.*)test_purge'
					]
				],

				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://www.example.org/test',
				'http://www.example.org/blog',
				'http://www.example.org/next_post',
				'http://www.example.org/author/author_name',
				'http://www.example.org/test_parent',
				'http://www.example.org/test_parent_2'
			]
		],


	]
];
