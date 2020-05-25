<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'                       => '',
						'..'                      => '',
						'home.css'                => '.home { color: red; }',
						'home-mobile.css'         => '.home { color: blue; }',
						'front_page.css'          => '.front_page { color: red; }',
						'front_page-mobile.css'   => '.front_page { color: blue; }',
						'category.css'            => '.category { color: red; }',
						'category-mobile.css'     => '.category { color: blue; }',
						'post_tag.css'            => '.post_tag { color: red; }',
						'post_tag-mobile.css'     => '.post_tag { color: blue; }',
						'page.css'                => '.page { color: red; }',
						'wptests_tax1.css'        => '.wptests_tax1 { color: red; }',
						'wptests_tax1-mobile.css' => '.wptests_tax1 { color: blue; }',
					],
				],
			],
		],
	],

	'test_data' => [

		'testShouldBailOutOnFilter' => [
			'config'   => [
				'version'         => 'default',
				'filters'         => [
					'do_rocket_critical_css_generation' => false,
				],
				'process_running' => false,

			],
			'expected' => [
				'generated'   => false,
				'items_count' => 1,
			],
		],

		'testShouldBailOutOnProcessAlreadyRunning' => [
			'config'   => [
				'version'         => 'default',
				'filters'         => [
					'do_rocket_critical_css_generation' => true,
				],
				'process_running' => true,

			],
			'expected' => [
				'generated'   => false,
				'items_count' => 1,
			],
		],

		'testShouldSucceed' => [
			'config'   => [
				'version'            => 'default',
				'filters'            => [
					'do_rocket_critical_css_generation' => true,
				],
				'process_running'    => false,
				'page_for_posts'     => 1,
				'page_for_posts_url' => 'http://www.example.com/?p=1',
				'show_on_front'      => 'page',
				'post_types'         => [ 'post' ],
				'posts'              => [
					(object) [
						'post_type'   => 'post',
						'ID'          => 1,
						'post_url'    => 'http://www.example.com/?p=2',
						'post_status' => 'publish',
					],
				],
				'taxonomies'         => [ 'category' ],
				'terms'              => [
					(object) [
						'taxonomy' => 'category',
						'ID'       => 1,
						'url'      => 'http://www.example.com/?cat=1',
					],
				],
			],
			'expected' => [
				'generated'   => true,
				'items_count' => 4,
			],
		],
	],
];
