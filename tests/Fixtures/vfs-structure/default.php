<?php

return [
	'wp-content' => [

		'cache' => [
			// Busting cache.
			'busting'      => [
				'1' => [
					'ga-123456.js' => '',
					'test.css'     => '',
					'test.css.gz'  => '',
					'test.js'      => '',
					'test.js.gz'   => '',
				],
			],
			// CPCSS cache.
			'critical-css' => [
				'index.php' => '<?php',
				'1'         => [
					'.'              => '',
					'..'             => '',
					'posts'          => [
						'.'  => '',
						'..' => '',
					],
					'home.css'       => '.p { color: red; }',
					'front_page.css' => '.p { color: red; }',
					'category.css'   => '.p { color: red; }',
					'post_tag.css'   => '.p { color: red; }',
					'page.css'       => '.p { color: red; }',
				],
				'2'         => [
					'posts'          => [
						'.'           => '',
						'..'          => '',
						'page-20.css' => '.p { color: red; }',
					],
					'home.css'       => '.p { color: red; }',
					'front_page.css' => '.p { color: red; }',
					'category.css'   => '.p { color: red; }',
					'post_tag.css'   => '.p { color: red; }',
					'page.css'       => '.p { color: red; }',
				],
			],
			// Minify cache.
			'min'          => [
				'1'         => [
					'5c795b0e3a1884eec34a989485f863ff.js'     => '',
					'5c795b0e3a1884eec34a989485f863ff.js.gz'  => '',
					'fa2965d41f1515951de523cecb81f85e.css'    => '',
					'fa2965d41f1515951de523cecb81f85e.css.gz' => '',
					'wp-content'                              => [
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
					'wp-includes'                             => [
						'css' => [
							'admin-bar-85585a650224ba853d308137b9a13487.css' => '',
							'dashicons-c2ba5f948753896932695bf9dad93d5e.css' => '',
						],
						'js'  => [
							'jquery'                                        => [
								'jquery-migrate-ca635e318ab90a01a61933468e5a72de.js' => '',
							],
							'admin-bar-65d8267e813dff6d0059914a4bc252aa.js' => '',
						],
					],
				],
				'2'         => [
					'34a989485f863ff5c795b0e3a1884eec.js'     => '',
					'34a989485f863ff5c795b0e3a1884eec.js.gz'  => '',
					'523cecb81f85efa2965d41f1515951de.css'    => '',
					'523cecb81f85efa2965d41f1515951de.css.gz' => '',
				],
				'3rd-party' => [
					'2n7x3vd41f1515951de523cecb81f85e.css'    => '',
					'2n7x3vd41f1515951de523cecb81f85e.css.gz' => '',
					'bt937b0e3a1884eec34a989485f863ff.js'     => '',
					'bt937b0e3a1884eec34a989485f863ff.js.gz'  => '',
				],
			],

			// WP Rocket's cache.
			'wp-rocket'    => [
				'index.html'                 => '',

				// Subdomain cache.
				'baz.example.org'            => [
					'.'                      => '',
					'..'                     => '',
					'index.html'             => '',
					'index.html_gzip'        => '',
					'index-mobile.html'      => '',
					'index-mobile.html_gzip' => '',
				],

				// domain cache.
				'example.org'                => [
					'index.html'             => '',
					'index.html_gzip'        => '',
					'index-mobile.html'      => '',
					'index-mobile.html_gzip' => '',
					'blog'                   => [
						'index.html'             => '',
						'index.html_gzip'        => '',
						'index-mobile.html'      => '',
						'index-mobile.html_gzip' => '',
					],
					'category'               => [
						'wordpress' => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
					],
					'de'                     => [
						'index.html'             => '',
						'index.html_gzip'        => '',
						'index-mobile.html'      => '',
						'index-mobile.html_gzip' => '',
					],
					'fr'                     => [
						'index.html'             => '',
						'index.html_gzip'        => '',
						'index-mobile.html'      => '',
						'index-mobile.html_gzip' => '',
					],
					'hidden-files'           => [
						'.mobile-active' => '',
						'.no-webp'       => '',
					],
					'lorem-ipsum'            => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'nec-ullamcorper'        => [
						'enim-nunc-faucibus' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'index.html'         => '',
						'index.html_gzip'    => '',
					],
				],

				// User cache.
				'example.org-wpmedia-123456' => [
					'index.html'      => '',
					'index.html_gzip' => '',
					'de'              => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'fr'              => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'lorem-ipsum'     => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],

				// User cache.
				'example.org-tester-987654'  => [
					'index.html'      => '',
					'index.html_gzip' => '',
					'de'              => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'fr'              => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'lorem-ipsum'     => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
					'nec-ullamcorper' => [
						'enim-nunc-faucibus' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'index.html'         => '',
						'index.html_gzip'    => '',
					],
				],
			],
		],

		'themes' => [
			'twentytwenty' => [
				'style.php' => 'test',
				'assets'    => [
					'script.php' => 'test',
				],
			],
		],

		'plugins' => [
			'hello-dolly' => [
				'style.php'  => '',
				'script.php' => '',
			],
			'wp-rocket'   => [
				'views'            => [
					'cache' => [
						'advanced-cache.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/cache/advanced-cache.php' ),
					],
				],
				'licence-data.php' => '',
			],
		],

		'uploads' => [],

		'wp-rocket-config' => [
			'example.org.php' => '<?php $var = "Some contents.";',
		],

		'advanced-cache.php' => '<?php $var = "Some contents.";',
	],
	'.htaccess'  => "# Random\n# add a trailing slash to /wp-admin\n# BEGIN WordPress\n# END WordPress\n",
	'wp-config.php' => "<?php\ndefine( 'DB_NAME', 'local' );\ndefine( 'DB_USER', 'root' );\n",
];
