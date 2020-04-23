<?php

return [
	'wp-content' => [
		'cache'            => [
			'wp-rocket'    => [
				'index.html'                 => '',
				'example.org'                => [
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
					'hidden-files'    => [
						'.mobile-active' => '',
						'.no-webp'       => '',
					],
					'lorem-ipsum'     => [
						'index.html'      => '',
						'index.html_gzip' => '',
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
				'dots.example.org'           => [
					'.'               => '',
					'..'              => '',
					'index.html'      => '',
					'index.html_gzip' => '',
				],
			],
			'min'          => [
				'1'         => [
					'5c795b0e3a1884eec34a989485f863ff.js'     => '',
					'5c795b0e3a1884eec34a989485f863ff.js.gz'  => '',
					'fa2965d41f1515951de523cecb81f85e.css'    => '',
					'fa2965d41f1515951de523cecb81f85e.css.gz' => '',
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
			'busting'      => [
				'1' => [
					'ga-123456.js' => '',
				],
			],
			'critical-css' => [
				'1' => [
					'front-page.php' => '',
					'blog.php'       => '',
				],
			],
		],
		'themes'  => [
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
			'wp-rocket' => [
				'licence-data.php' => '',
			],
		],
		'wp-rocket-config' => [
			'example.org.php' => 'test',
		],
	],
];
