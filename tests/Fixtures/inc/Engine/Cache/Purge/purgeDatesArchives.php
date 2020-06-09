<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                => [
						'2020' => [
							'.no-webp'              => '',
							'index-https.html'      => '',
							'index-https.html_gzip' => '',
							'03' => [
								'.no-webp'              => '',
								'index-https.html'      => '',
								'index-https.html_gzip' => '',
								'01' => [
									'.no-webp'              => '',
									'index-https.html'      => '',
									'index-https.html_gzip' => '',
								],
							],
						],
						'2019' => [
							'.mobile-active'         => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
							'10' => [
								'.mobile-active'         => '',
								'index-mobile.html'      => '',
								'index-mobile.html_gzip' => '',
								'24' => [
									'.mobile-active'         => '',
									'index-mobile.html'      => '',
									'index-mobile.html_gzip' => '',
								],
							],
						],
						'2012' => [
							'index-https-webp.html'      => '',
							'index-https-webp.html_gzip' => '',
							'05'                           => [
								'index-https-webp.html'      => '',
								'index-https-webp.html_gzip' => '',
								'13' => [
									'index-https-webp.html'      => '',
									'index-https-webp.html_gzip' => '',
								],
							],
							'pages' => [
								'2' => [
									'index-https-webp.html'      => '',
									'index-https-webp.html_gzip' => '',
								],
								'3' => [
									'index-https-webp.html'      => '',
									'index-https-webp.html_gzip' => '',
								],
							],
						],
					],
					'example.org-wpmedia-123456' => [
						'2020' => [
							'.no-webp'              => '',
							'index-https.html'      => '',
							'index-https.html_gzip' => '',
							'03' => [
								'.no-webp'              => '',
								'index-https.html'      => '',
								'index-https.html_gzip' => '',
								'01' => [
									'.no-webp'              => '',
									'index-https.html'      => '',
									'index-https.html_gzip' => '',
								],
							],
						],
						'2019' => [
							'.mobile-active'         => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
							'10' => [
								'.mobile-active'         => '',
								'index-mobile.html'      => '',
								'index-mobile.html_gzip' => '',
								'24' => [
									'.mobile-active'         => '',
									'index-mobile.html'      => '',
									'index-mobile.html_gzip' => '',
								],
							],
						],
						'2012' => [
							'index-https-webp.html'      => '',
							'index-https-webp.html_gzip' => '',
							'05'                           => [
								'index-https-webp.html'      => '',
								'index-https-webp.html_gzip' => '',
								'13' => [
									'index-https-webp.html'      => '',
									'index-https-webp.html_gzip' => '',
								],
							],
							'pages' => [
								'2' => [
									'index-https-webp.html'      => '',
									'index-https-webp.html_gzip' => '',
								],
								'3' => [
									'index-https-webp.html'      => '',
									'index-https-webp.html_gzip' => '',
								],
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldCleanCacheNoWebP' => [
			'post'    => [
				'post_title'   => 'Lorem ipsum',
				'post_content' => 'Lorem ipsum dolor sit amet',
				'post_status'  => 'publish',
				'post_date'    => '2020-03-01',
			],
			'cleaned' => [
				'wp-content/cache/wp-rocket/example.org/2020/.no-webp',
				'wp-content/cache/wp-rocket/example.org/2020/index-https.html',
				'wp-content/cache/wp-rocket/example.org/2020/index-https.html_gzip',
				'wp-content/cache/wp-rocket/example.org/2020/03/.no-webp',
				'wp-content/cache/wp-rocket/example.org/2020/03/index-https.html',
				'wp-content/cache/wp-rocket/example.org/2020/03/index-https.html_gzip',
				'wp-content/cache/wp-rocket/example.org/2020/03/01/.no-webp',
				'wp-content/cache/wp-rocket/example.org/2020/03/01/index-https.html',
				'wp-content/cache/wp-rocket/example.org/2020/03/01/index-https.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/.no-webp',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/index-https.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/index-https.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/03/.no-webp',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/03/index-https.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/03/index-https.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/03/01/.no-webp',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/03/01/index-https.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-123456/2020/03/01/index-https.html_gzip',
			],
		],
		'testShouldCleanCacheMobileActive' => [
			'post'    => [
				'post_title'   => 'Nec ullamcorper',
				'post_content' => 'Nec ullamcorper sit amet risus nullam eget.',
				'post_status'  => 'publish',
				'post_date'    => '2019-10-24',
			],
			'cleaned' => [

			],
		],
		'testShouldCleanCachePagination' => [
			'post'    => [
				'post_title'   => 'Enim nunc faucibus',
				'post_content' => 'Enim nunc faucibus a pellentesque sit amet porttitor eget.',
				'post_status'  => 'draft',
				'post_date'    => '2012-05-13',
			],
			'cleaned' => [

			],
		],
		'testShouldNotCleanCacheWhenPage' => [
			'post'    => [
				'post_title'   => 'Semper viverra nam libero justo',
				'post_content' => 'Semper viverra nam libero justo. Blandit cursus risus at ultrices mi tempus imperdiet nulla.',
				'post_status'  => 'pending',
				'post_type'    => 'page',
				'post_date'    => '2022-06-30',
			],
			'cleaned' => false,
		],
	],
];