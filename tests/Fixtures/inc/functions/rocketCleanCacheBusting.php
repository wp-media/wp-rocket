<?php

return [
	'vfs_dir'   => 'wp-content/cache/busting/1/',

	// Test data.
	'test_data' => [
		'shouldNotCleanWhenNoExtensionsGiven'     => [
			'extensions' => '',
			'expected'   => [
				'cleaned' => [],
			],
		],
		'shouldNotCleanWhenExtensionDoesNotExist' => [
			'extensions' => [ 'php', 'html' ],
			'expected'   => [
				'cleaned' => [],
			],
		],
		'shouldClean_css'                         => [
			'extensions' => 'css',
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/busting/1/wp-content/test.css'    => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/test.css.gz' => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/'            => null,
				],
			],
		],
		'shouldClean_js'                          => [
			'extensions' => 'js',
			'expected'   => [
				'cleaned'      => [
					'vfs://public/wp-content/cache/busting/1/wp-content/test.js'    => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/test.js.gz' => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/'           => null,
				],
			],
		],
		'shouldCleanCssAndJs'                     => [
			'extensions' => [ 'css', 'js' ],
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/busting/1/wp-content/test.js'     => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/test.js.gz'  => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/test.css'    => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/test.css.gz' => null,
					'vfs://public/wp-content/cache/busting/1/wp-content/'            => null,
				],
			],
		],
	],
];
