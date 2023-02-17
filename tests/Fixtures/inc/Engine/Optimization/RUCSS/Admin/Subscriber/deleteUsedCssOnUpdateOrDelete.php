<?php

$items = [
	[
		'url'            => 'http://example.org/path1',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/path2',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
];

$files = [
	'vfs://public/wp-content/cache/used-css/1/slug_0/',
	'vfs://public/wp-content/cache/used-css/1/slug_0/used.css',
	'vfs://public/wp-content/cache/used-css/1/slug_0/used-mobile.css',
	'vfs://public/wp-content/cache/used-css/1/slug_1/',
	'vfs://public/wp-content/cache/used-css/1/slug_1/used.css',
	'vfs://public/wp-content/cache/used-css/1/slug_1/used-mobile.css',
];

$preserved = [
	'vfs://public/wp-content/cache/used-css/1/slug_preserved/',
	'vfs://public/wp-content/cache/used-css/1/slug_preserved/used.css',
	'vfs://public/wp-content/cache/used-css/1/slug_preserved/used-mobile.css',
];

return [

	'vfs_dir' => 'wp-content/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'used-css' => [
					1 => [
						'slug_0' => [
							'used.css' => '',
							'used-mobile.css' => '',
						],
						'slug_1' => [
							'used.css' => '',
							'used-mobile.css' => '',
						],
						'slug_preserved' => [
							'used.css' => '',
							'used-mobile.css' => '',
						],
					],
				],
			],
		],
	],

	'test_data' => [
		'shouldNotDeleteOnUpdateDueToMissingSettings' => [
			'input' => [
				'remove_unused_css' => false,
				'items'             => $items,
				'wp_error' => false,
				'post_id' => 1,
				'url' => 'http://example.org/category/test/',
				'files_deleted'     => [],
				'files_preserved'   => array_merge( $files, $preserved ),
			]
		],
		'shouldDeleteOnUpdate' => [
			'input' => [
				'remove_unused_css' => true,
				'deletion_activated' => true,
				'items'             => $items,
				'files_deleted'     => [],
				'wp_error' => false,
				'post_id' => 1,
				'url' => 'http://example.org/category/test/',
				'files_preserved'   => array_merge( $files, $preserved ),
			]
		],
		'shouldNotDeleteOnDisabledFilter' => [
			'input' => [
				'remove_unused_css' => true,
				'is_disabled' => false,
				'wp_error' => false,
				'post_id' => 1,
				'url' => 'http://example.org/category/test/',
				'items'             => $items,
				'files_deleted'     => [],
				'files_preserved'   => array_merge( $files, $preserved ),
			]
		]
	]

];
