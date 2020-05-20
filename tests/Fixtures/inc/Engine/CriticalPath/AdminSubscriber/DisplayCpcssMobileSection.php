<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'cpcss' => [
							'activate-cpcss-mobile.php' =>
								file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/activate-cpcss-mobile.php' )
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldBailOutWithNoCapability' => [
			'config' => [
				'current_user_can'        => false,
				'options'                 => [
					'async_css'               => 1,
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 1,
					'async_css_mobile'        => 1
				],
			],
			'expected' => ''
		],
		'testShouldBailOutWithNoAsyncCss' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 0,
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 1,
					'async_css_mobile'        => 0
				],
			],
			'expected' => ''
		],
		'testShouldBailOutWithNoCacheMobile' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 1,
					'cache_mobile'            => 0,
					'do_caching_mobile_files' => 1,
					'async_css_mobile'        => 0
				],
			],
			'expected' => ''
		],
		'testShouldBailOutWithNoDoCacheMobileFiles' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 1,
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 0,
					'async_css_mobile'        => 0
				],
			],
			'expected' => ''
		],
		'testShouldBailOutWithNoOption' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 0,
					'cache_mobile'            => 0,
					'do_caching_mobile_files' => 0,
					'async_css_mobile'        => 0
				],
			],
			'expected' => ''
		],
		'testSucceedWithAllOptionsNotAsyncCssMobile' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 1,
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 1,
					'async_css_mobile'        => 0
				],
			],
			'expected' => ''
		],
		'testSucceedWithAsyncCssMobile' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 0,
					'cache_mobile'            => 0,
					'do_caching_mobile_files' => 0,
					'async_css_mobile'        => 1
				],
			],
			'expected' => ''
		],
		'testSucceedWithAllOptions' => [
			'config' => [
				'current_user_can'        => true,
				'options'                 => [
					'async_css'               => 1,
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 1,
					'async_css_mobile'        => 1
				],
			],
			'expected' => ''
		]
	]

];
