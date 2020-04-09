<?php

return [
	'vfs_dir'   => 'wp-content/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache'            => [
				'wp-rocket' => [
					'example.org'                                => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'about'           => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
						'blog'            => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'category'        => [
							'wordpress' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
						],
						'en'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
				'min'       => [
					'1' => [
						'wp-content'  => [
							'plugins' => [
								'imagify' => [
									'assets' => [
										'css' => [
											'admin-bar-924d9d45c4af91c09efb7ad055662025.css' => '',
											'admin-bar-bce302f71910a4a126f7df01494bd6e0.css' => '',
										],
										'js'  => [
											'admin-bar-171a2ef75c22c390780fe898f9d40c8d.js' => '',
											'admin-bar-e4aa3c9df56ff024286f4df600f4c643.js' => '',
										],
									],
								],
							],
						],
						'wp-includes' => [
							'css' => [
								'admin-bar-85585a650224ba853d308137b9a13487.css' => '',
								'dashicons-c2ba5f948753896932695bf9dad93d5e.css' => '',
							],
							'js'  => [
								'jquery' => [
									'jquery-migrate-ca635e318ab90a01a61933468e5a72de.js' => '',
								],
								'admin-bar-65d8267e813dff6d0059914a4bc252aa.js' => '',
							],
						],
					],
				],
			],
			'wp-rocket-config' => [
				'example.org.php' => '<?php $var = "Some contents.";',
			],
		],
	],

	// Test data.
	'test_data' => [
		[
			'rocket_clean_domain'         => [
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org/about/index.html',
				'wp-content/cache/wp-rocket/example.org/about/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org/about/index-mobile.html',
				'wp-content/cache/wp-rocket/example.org/about/index-mobile.html_gzip',
				'wp-content/cache/wp-rocket/example.org/blog/index.html',
				'wp-content/cache/wp-rocket/example.org/blog/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/index.html',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org/en/index.html',
				'wp-content/cache/wp-rocket/example.org/en/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip',
			],
			'flush_rocket_htaccess'       => [
				'.htaccess',
			],
			'rocket_generate_config_file' => [
				'wp-content/wp-rocket-config/wp-rocket-config/example.org.php',
			],
			'rocket_clean_minify_css'     => [
				'wp-content/min/1/wp-content/plugins/imagify/assets/css/admin-bar-924d9d45c4af91c09efb7ad055662025.css',
				'wp-content/min/1/wp-content/plugins/imagify/assets/css/admin-bar-bce302f71910a4a126f7df01494bd6e0.css',
				'wp-content/min/1/wp-includes/css/admin-bar-85585a650224ba853d308137b9a13487.css',
				'wp-content/min/1/wp-includes/css/dashicons-c2ba5f948753896932695bf9dad93d5e.css',
			],
			'rocket_clean_minify_js'      => [
				'wp-content/min/1/wp-content/plugins/imagify/assets/js/admin-bar-171a2ef75c22c390780fe898f9d40c8d.js',
				'wp-content/min/1/wp-content/plugins/imagify/assets/js/admin-bar-e4aa3c9df56ff024286f4df600f4c643.js',
				'wp-content/min/1/wp-includes/js/jquery/jquery-migrate-ca635e318ab90a01a61933468e5a72de.js',
				'wp-content/min/1/wp-includes/js/admin-bar-65d8267e813dff6d0059914a4bc252aa.js',
			],
			'rocket_generate_advanced_cache_file' => [
				'wp-content/advanced-cache.php',
			],
		],
	],
];
