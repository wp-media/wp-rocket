<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'                => '',
						'..'               => '',
						'posts'            => [
							'.'                  => '',
							'..'                 => '',
							'post-1.css'         => '.post-1 { color: red; }',
							'post-1-mobile.css'  => '.post-1-mobile { color: red; }',
							'post-10.css'        => '.post-10 { color: red; }',
							'post-20.css'        => '.post-20 { color: red; }',
							'post-20-mobile.css' => '.post-20-mobile { color: red; }',
						],
						'home.css'         => '.home { color: red; }',
						'front_page.css'   => '.front_page { color: red; }',
						'category.css'     => '.category { color: red; }',
						'post_tag.css'     => '.post_tag { color: red; }',
						'page.css'         => '.page { color: red; }',
						'wptests_tax1.css' => '.wptests_tax1 { color: red; }',
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldBailOutNoPermissions' => [
			'config'   => [
				'current_user_can' => false,
			],
			'expected' => [
				'desktop' => false,
				'mobile'  => false,
			],
		],
		'testShouldBailoutNoAsync'       => [
			'config'   => [
				'current_user_can' => true,
				'async_css'        => false,
				'async_css_mobile' => false,
			],
			'expected' => [
				'desktop' => false,
				'mobile'  => false,
			],
		],
		'testShouldDeleteOnlyDesktop'    => [
			'config'   => [
				'current_user_can' => true,
				'async_css'        => true,
				'async_css_mobile' => false,
				'post' => [
					'type' => 'post',
					'id'   => 10,
				],
				'desktop' => 'posts' . DIRECTORY_SEPARATOR . "page-10.css",
				'mobile'  => 'posts' . DIRECTORY_SEPARATOR . "page-10-mobile.css",
			],
			'expected' => [
				'desktop' => true,
				'mobile'  => false,
			],
		],
		'testShouldDeleteBoth'           => [
			'config'   => [
				'current_user_can' => true,
				'async_css'        => true,
				'async_css_mobile' => true,
				'post' => [
					'type' => 'post',
					'id'   => 1,
				],
				'desktop' => 'posts' . DIRECTORY_SEPARATOR . "post-1.css",
				'mobile'  => 'posts' . DIRECTORY_SEPARATOR . "post-1-mobile.css",
			],
			'expected' => [
				'desktop' => true,
				'mobile'  => true,
			],
		],
	],
];
