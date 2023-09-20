<?php

return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'test_purge' => [
							'index.html' => ''
						],
						'folder_1' => [
							'test_purge' => [
								'index.html' => ''
							]
						]
					]
				]
			]
		]
	],

	'urls' => [
		'site_url'=> 'http://example.org/wp/',
		'home_url'=> 'http://example.org/',
		'posts' => [
			20 => 'http://example.org/blog/',
			2 => 'http://example.org/next_post/',
			3 => 'http://example.org/test_parent/',
			4 => 'http://example.org/test_parent_2/',
			5 => 'http://example.org/test2/',
			6 => 'http://example.org/test3/'
		],
		'authors' => [
			1 => 'http://example.org/author/author_name/',
			2 => 'http://example.org/',
			3 => 'http://example.org/wp/',
		],
		'archives' => [
			'page' => false,
			'post' => 'http://example.org/',
			'custompost' => 'http://example.org/custompost/',
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
					'url' => '/test/',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://example.org/test/',
				'http://example.org/blog/',
				'http://example.org/next_post/',
				'http://example.org/author/author_name/',
				'http://example.org/test_parent/',
				'http://example.org/test_parent_2/'
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
					'post_name' => 'test_page',
					'url' => '/test_page/',
					'post_type' => 'page',
					'next_post_id' => 20,
					'post_author' => 1
				],

			],
			'expected' => [
				'http://example.org/test_page/',
				'http://example.org/blog/',
				'http://example.org/author/author_name/'
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
					'url' => '/custompost/test_custom_post/',
					'post_type' => 'custompost',
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				],

				'is_ssl' => false,
			],
			'expected' => [
				'http://example.org/custompost/test_custom_post/',
				'http://example.org/custompost/index.html',
				'http://example.org/custompost/index.html_gzip',
				'http://example.org/custompost/page',
				'http://example.org/author/author_name/',
				'http://example.org/test_parent/',
				'http://example.org/test_parent_2/'
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
					'post_name' => 'test_prev',
					'url' => '/test_prev/',
					'post_type' => 'post',
					'next_post_id' => 2,
					'prev_post_id' => 5,
					'prev_in_term_post_id' => 6,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://example.org/test_prev/',
				'http://example.org/blog/',
				'http://example.org/next_post/',
				'http://example.org/test2/',
				'http://example.org/test3/',
				'http://example.org/author/author_name/',
				'http://example.org/test_parent/',
				'http://example.org/test_parent_2/'
			]
		],

		'shouldReturnPostUrlsWithPurgeUrls' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'home' => 'http://example.org',
					'cache_purge_pages' => [
						'(.*)test_purge'
					]
				],

				'post_data' => [
					'ID' => 1,
					'post_name' => 'test_purge_post',
					'url' => '/test_purge_post/',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 1,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://example.org/test_purge_post/',
				'http://example.org/blog/',
				'http://example.org/next_post/',
				'http://example.org/test_purge',
				'http://example.org/folder_1/test_purge',
				'http://example.org/author/author_name/',
				'http://example.org/test_parent/',
				'http://example.org/test_parent_2/'
			]
		],

		'shouldNotAddAuthorURLSameSiteUrl' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'cache_purge_pages' => false
				],

				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test/',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 2,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://example.org/test/',
				'http://example.org/blog/',
				'http://example.org/next_post/',
				'http://example.org/test_parent/',
				'http://example.org/test_parent_2/'
			]
		],
		'shouldNotAddAuthorURLSameHomeUrl' => [
			'config' => [
				'options' => [
					'page_for_posts' => 20,
					'cache_purge_pages' => false
				],

				'post_data' => [
					'ID' => 1,
					'post_name' => 'test',
					'url' => '/test/',
					'post_type' => 'post',
					'next_post_id' => 2,
					'post_author' => 3,
					'ancestors' => [ 3, 4 ]
				]
			],
			'expected' => [
				'http://example.org/test/',
				'http://example.org/blog/',
				'http://example.org/next_post/',
				'http://example.org/test_parent/',
				'http://example.org/test_parent_2/'
			]
		],
	]
];
