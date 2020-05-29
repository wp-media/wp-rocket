<?php
return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                => [
						'test.html' => '',
						'index.html_gzip' => '',
						'index.html' => '',
						'index-test.html' => '',
						'.mobile-active' => '',
						'.no-webp' => '',
						'page' => [
							'1' => [
								'index.html_gzip' => '',
								'index.html' => '',
							],
							'2' => [
								'index.html_gzip' => '',
								'index.html' => '',
							],
						]
					],
					'example.org-wpmedia-123456' => [
						'index.html_gzip' => '',
					],
					'example.org-tester-987654'  => [
						'index.html_gzip' => '',
					],

					'baz.example.org'             => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz1-123456' => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz2-987654' => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz3-456789' => [
						'index.html_gzip' => '',
					],

					'wp.baz.example.org'               => [
						'index.html_gzip' => '',
					],
					'wp.baz.example.org-wpbaz1-123456' => [
						'index.html_gzip' => '',
					],

					'example.org#fr' => [
						'index.html_gzip' => '',
					],
				],
			],
		],
	],

	'test_data' => [
		'testShouldRemoveFilesForMainDomain' => [
			'config' => [
				'home_url' => 'http://example.org',
			],
			'expected' => [
				'removed_files' => [
					'example.org/index.html_gzip',
					'example.org/index.html',
					'example.org/index-test.html',
					'example.org/.mobile-active',
					'example.org/.no-webp',
					'example.org/page/1/index.html_gzip',
					'example.org/page/1/index.html',
					'example.org/page/2/index.html_gzip',
					'example.org/page/2/index.html',
					'example.org-wpmedia-123456/index.html_gzip',
					'example.org-tester-987654/index.html_gzip',

					'example.org#fr/index.html_gzip'
				],
				'not_removed_files' => [
					'example.org/test.html',

					'baz.example.org/index.html_gzip',
					'baz.example.org-baz1-123456/index.html_gzip',
					'baz.example.org-baz2-987654/index.html_gzip',
					'baz.example.org-baz3-456789/index.html_gzip',

					'wp.baz.example.org/index.html_gzip',
					'wp.baz.example.org-wpbaz1-123456/index.html_gzip'
				],
			]
		],
		'testShouldRemoveFilesForSubDomain' => [
			'config' => [
				'home_url' => 'http://baz.example.org',
			],
			'expected' => [
				'removed_files' => [
					'baz.example.org/index.html_gzip',
					'baz.example.org-baz1-123456/index.html_gzip',
					'baz.example.org-baz2-987654/index.html_gzip',
					'baz.example.org-baz3-456789/index.html_gzip'
				],
				'not_removed_files' => [
					'example.org/test.html',

					'example.org/index.html_gzip',
					'example.org/index.html',
					'example.org/index-test.html',
					'example.org/.mobile-active',
					'example.org/.no-webp',
					'example.org/page/1/index.html_gzip',
					'example.org/page/1/index.html',
					'example.org/page/2/index.html_gzip',
					'example.org/page/2/index.html',
					'example.org-wpmedia-123456/index.html_gzip',
					'example.org-tester-987654/index.html_gzip',

					'wp.baz.example.org/index.html_gzip',
					'wp.baz.example.org-wpbaz1-123456/index.html_gzip',

					'example.org#fr/index.html_gzip'
				],
			]
		],
		'testShouldRemoveFilesForSubSubDomain' => [
			'config' => [
				'home_url' => 'http://wp.baz.example.org',
			],
			'expected' => [
				'removed_files' => [
					'wp.baz.example.org/index.html_gzip',
					'wp.baz.example.org-wpbaz1-123456/index.html_gzip'
				],
				'not_removed_files' => [
					'example.org/test.html',

					'example.org/index.html_gzip',
					'example.org/index.html',
					'example.org/index-test.html',
					'example.org/.mobile-active',
					'example.org/.no-webp',
					'example.org/page/1/index.html_gzip',
					'example.org/page/1/index.html',
					'example.org/page/2/index.html_gzip',
					'example.org/page/2/index.html',
					'example.org-wpmedia-123456/index.html_gzip',
					'example.org-tester-987654/index.html_gzip',

					'baz.example.org/index.html_gzip',
					'baz.example.org-baz1-123456/index.html_gzip',
					'baz.example.org-baz2-987654/index.html_gzip',
					'baz.example.org-baz3-456789/index.html_gzip',

					'example.org#fr/index.html_gzip'
				],
			]
		],

	]
];
