<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'            => '',
						'..'           => '',
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
						'posts'        => [],
					],
					'2' => [
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'non_multisite' => [
			'testShouldDoNothingWhenAsynCssOff' => [
				'values'   => [
					'old' => [
						'async_css'               => 0,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
					'new' => [
						'async_css'               => 0,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
				],
				'mobile'   => false,
				'expected' => false,
			],
			'testShouldDoNothingWhenAsynCssDisabled' => [
				'values'   => [
					'old' => [
						'async_css'               => 1,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
					'new' => [
						'async_css'               => 0,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
				],
				'mobile' => false,
				'expected' => false,
			],
			'testShouldDoNothingWhenAsynCSSEnabledAndDidntChange' => [
				'values'   => [
					'old' => [
						'async_css'               => 1,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
					'new' => [
						'async_css'               => 1,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
				],
				'mobile'   => false,
				'expected' => false,
			],
			'testShouldDoNothingWhenFilesAlreadyExist' => [
				'values'   => [
					'old' => [
						'async_css'               => 0,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
					'new' => [
						'async_css'               => 1,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
				],
				'mobile'   => false,
				'expected' => false,
			],

			'testShouldGenerateWhenFilesDontExist' => [
				'values'   => [
					'old' => [
						'async_css'               => 0,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
					'new' => [
						'async_css'               => 1,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
				],
				'mobile'   => 'default',
				'expected' => true,
			],

			'testShouldGenerateWhenFilesDontExistAndMobile' => [
				'values'   => [
					'old' => [
						'async_css'               => 0,
						'do_caching_mobile_files' => 0,
						'async_css_mobile'        => 0,
					],
					'new' => [
						'async_css'               => 1,
						'do_caching_mobile_files' => 1,
						'async_css_mobile'        => 1,
					],
				],
				'mobile'   => 'all',
				'expected' => true,
			],
		],

		'multisite' => [
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 0 ],
				],
				'blog_id'         => 2,
				'should_generate' => false,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'blog_id'         => 2,
				'should_generate' => false,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'blog_id'         => 2,
				'should_generate' => false,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 1 ],
					'new' => [ 'async_css' => 1 ],
				],
				'blog_id'         => 2,
				'should_generate' => false,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'site_id'         => 3,
				'should_generate' => true,
			],
			[
				'values'          => [
					'old' => [ 'async_css' => 0 ],
					'new' => [ 'async_css' => 1 ],
				],
				'site_id'         => 4,
				'should_generate' => true,
			],
		],
	],
];
