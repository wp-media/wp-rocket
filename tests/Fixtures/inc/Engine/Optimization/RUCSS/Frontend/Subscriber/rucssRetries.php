<?php

return [
	'vfs_dir' => 'public/',

	'structure' => [
		'wp-content' => [
			'themes' => [
				'theme-name' => [
					'style.css' => '.theme-name{color:red;}'
				]
			],
			'cache' => [
				'unused-css' => [
					'leaveMeHere.css' => 'random css file contents',
					'page1.css' => '.first{color:red;}',
					'page2.css' => '.second{color:green;}',
					'page3.css' => '.third{color:blue;}',
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
						'url'            => 'example.com/wp-content/cache/unused-css/page1.css',
						'css'            => '.example{color:red;}',
						'unprocessedcss' => json_encode( [] ),
						'retries'        => '1',
					],
					[
						'id'             => '2',
						'url'            => 'vfs://public/wp-content/cache/unused-css/page2.css',
						'css'            => '.example{color:green;}',
						'unprocessedcss' => json_encode( [
							'styles/mystyle.css'
						] ),
						'retries'        => '3',
					],
					[
						'id'             => '3',
						'url'            => 'vfs://public/wp-content/cache/unused-css/page3.css',
						'css'            => '.example{color:blue;}',
						'unprocessedcss' => json_encode( [
							'styles/yourstyle.css',
							'js/myslides.js',
						] ),
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
				'files-after' => [
					'leaveMeHere.css',
					'page1.css',
					'page2.css',
					'page3.css',
				],
			],
		],

		'shouldIgnoreEntriesWithNoUnusedCSS' => [
			'config'   => [
				'rucss-enabled' => true,
				'items'         => [
					[
						'id'             => '1',
						'url'            => 'vfs://public/wp-content/cache/unused-css/page1.css',
						'css'            => '.example{color:red;}',
						'unprocessedcss' => json_encode( [] ),
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
				'files-after' => [
					'leaveMeHere.css',
					'page1.css',
					'page2.css',
					'page3.css',
				],
			],
		],

		'shouldPurgeAndResetRetriesOfItemsWithUnusedCssTo1' => [
			'config'   => [
				'rucss-enabled' => true,
				'items'         => [
					[
						'id'             => '2',
						'url'            => 'https://example.com/wp-content/cache/unused-css/page2.css',
						'css'            => '.example{color:green;}',
						'unprocessedcss' => json_encode( [
							'styles/mystyle.css'
						] ),
						'retries'        => '3',
					],
					[
						'id'             => '3',
						'url'            => 'https://example.com/wp-content/cache/unused-css/page3.css',
						'css'            => '.example{color:blue;}',
						'unprocessedcss' => json_encode( [
							'styles/yourstyle.css',
							'js/myslides.js',
						] ),
						'retries'        => '3',
					],
				],
			],
			'expected' => [
				'items-after' => [
					[
						'id'      => '2',
						'retries' => '1',
					],
					[
						'id' => '3',
						'retries' => '1',
					],
				],
				'files-after' => [
					'leaveMeHere.css',
					'page1.css',
				]
			],
		],
	],
];
