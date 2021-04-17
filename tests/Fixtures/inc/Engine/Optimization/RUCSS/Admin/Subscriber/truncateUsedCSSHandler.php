<?php

$items = [
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => true,
	],
];

$cache_files = [
	'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                                     => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                                => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html'      => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip' => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html'          => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html_gzip'     => null,
];

return [
	'vfs_dir' => 'wp-content/',

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
			],
		],
	],
	'test_data' => [

		'shouldNotTruncateUnusedCSSWithNotExistsNonce' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => null,
				'db_items' => $items,
				'cache_files' => $cache_files,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'nonce'
			],
		],

		'shouldNotTruncateUnusedCSSWithInvalidNonce' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'invalid',
				'db_items' => $items,
				'cache_files' => $cache_files,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'nonce'
			],
		],

		'shouldNotTruncateUnusedCSSWhenCurrentUserCant' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'rocket_clear_usedcss',
				'cap'     => false,
				'db_items' => $items,
				'cache_files' => $cache_files,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'cap'
			],
		],

		'shouldNotTruncateUnusedCSSWhenOptionDisabled' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'rocket_clear_usedcss',
				'cap'     => true,
				'option_enabled' => false,
				'db_items' => $items,
				'cache_files' => $cache_files,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'option',
				'notice_details' => [
					'status'  => 'error',
					'message' => 'Used CSS option is not enabled!',
				],
			],
		],

		'shouldTruncateUnusedCSS' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'rocket_clear_usedcss',
				'cap'     => true,
				'option_enabled' => true,
				'db_items' => $items,
				'cache_files' => $cache_files,
			],
			'expected' => [
				'truncated' => true,
				'notice_details' => [
					'status'  => 'success',
					'message' => 'Used CSS cache cleared!',
				],
			],
		],

	],
];
