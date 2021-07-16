<?php

$current_date = current_time( 'mysql', true );
$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 32 days' ) );

$used_css = [
	[
		'url'            => 'http://example.org/home/',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1/',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
];

$resources = [
	[
		'url'           => 'http://example.org/wp-content/themes/theme-name/style.css',
		'content'       => '.theme-name{color:red;}',
		'type'          => 'css',
		'media'         => 'all',
		'modified'      => $old_date,
		'last_accessed' => $old_date,
	],
	[
		'url'           => 'http://example.org/css/style.css',
		'content'       => '.first{color:green;}',
		'type'          => 'css',
		'media'         => 'all',
		'modified'      => $current_date,
		'last_accessed' => $current_date,
	]
];

return [
	'vfs_dir' => 'wp-content/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                                => [
						'index.html'      => '',
						'index.html_gzip' => '',
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

				'used-css' => [
					'1' => [
						'home' => [
							'used.css' => '',
							'used-mobile.css' => '',
						],
						'category' => [
							'level1' => [
								'used.css' => '',
								'used-mobile.css' => '',
							]
						]
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'shouldNotDeleteOnUpdateDueToMissingSettings' => [
			'input' => [
				'remove_unused_css'      => false,
				'used_css'               => $used_css,
				'resources'              => $resources,
				'deleted_used_css_files' => [],
			]
		],
		'shouldDeleteOnUpdate' => [
			'input' => [
				'remove_unused_css' => true,
				'used_css'          => $used_css,
				'resources'         => $resources,
				'deleted_used_css_files' => [
					'vfs://public/wp-content/cache/used-css/1/home/used.css' => null,
					'vfs://public/wp-content/cache/used-css/1/home/used-mobile.css' => null,
					'vfs://public/wp-content/cache/used-css/1/category/level1/used.css' => null,
					'vfs://public/wp-content/cache/used-css/1/category/level1/used-mobile.css' => null,
				],
			]
		],
	],
];
