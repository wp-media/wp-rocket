<?php

return [
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                                => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'page1' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'page2'      => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'page3'      => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-Foo-594d03f6ae698691165999'     => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
			],
		],
	],

	'test_data' => [
		'shouldBailOutWhenRucssDisabled' => [
			'config'   => [
				'rucss-enabled' => false,
				'items'         => [
					[
						'id'             => '1',
						'url'            => 'http://example.org/page1.html',
						'css'            => '.example{color:red;}',
						'retries'        => '1',
					],
					[
						'id'             => '2',
						'url'            => 'http://example.org/page2.html',
						'css'            => '.example{color:green;}',
						'retries'        => '3',
					],
					[
						'id'             => '3',
						'url'            => 'http://example.org/page3.html',
						'css'            => '.example{color:blue;}',
						'retries'        => '3',
					],
				],
			],
			'expected' => [
				'items-after' => [
					[
						'id'      => '1',
						'retries' => '1',
					],
					[
						'id'      => '2',
						'retries' => '3',
					],
					[
						'id'      => '3',
						'retries' => '3',
					],
				],
				'purged-files' => [],
			],
		],

		'shouldIgnoreEntriesWithNoUnusedCSS' => [
			'config'   => [
				'rucss-enabled' => true,
				'items'         => [
					[
						'id'             => '1',
						'url'            => 'http://example.org/page1.css',
						'css'            => '.example{color:red;}',
						'retries'        => '3',
					],
				],
			],
			'expected' => [
				'items-after' => [
					[
						'id'      => '1',
						'retries' => '3',
					],
				],
				'purged-files' => [],
			],
		],

		'shouldPurgeAndResetRetriesOfItemsWithUnusedCssTo1' => [
			'config'   => [
				'rucss-enabled' => true,
				'items'         => [
					[
						'id'             => '2',
						'url'            => 'http://example.org/page2/',
						'css'            => '.example{color:green;}',
						'retries'        => '3',
					],
					[
						'id'             => '3',
						'url'            => 'http://example.org/page3/',
						'css'            => '.example{color:blue;}',
						'retries'        => '3',
					],
				],
			],
			'expected' => [
				'items-after'  => [
					[
						'id'      => '2',
						'retries' => '1',
					],
					[
						'id'      => '3',
						'retries' => '1',
					],
				],
				'purged-files' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/page2/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/page2/index.html_gzip',
					'vfs://public/wp-content/cache/wp-rocket/example.org/page3/index.html',
					'vfs://public/wp-content/cache/wp-rocket/example.org/page3/index.html_gzip',
				]
			],
		],
	],
];
