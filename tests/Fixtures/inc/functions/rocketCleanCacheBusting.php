<?php

$structure = require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php';
$structure['releases+ (site):'] = [
	'wp-content' => [
		'cache' => [
			'busting' => [
				'1' => [ 'wp-content' => [ 'test.js' => '' ] ],
			],
		],
	],
];
$structure['C:\\releases+ (site):\\wp-content'] = [
	'cache' => [
		'busting' => [
			'1' => [ 'wp-content' => [ 'test.js' => '' ] ],
		],
	],
];

return [
	'structure' => $structure,
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
		'shouldClean_js_fromTimestampedPath'      => [
			'extensions'    => 'js',
			'expected'      => [
				'cleaned' => [
					'vfs://public/releases+ (site):/wp-content/cache/busting/1/wp-content/test.js' => null,
				],
			],
			'wp_content_dir' => 'vfs://public/releases+ (site):/wp-content',
		],
		'shouldClean_js_fromWindowsStylePath'     => [
			'extensions'    => 'js',
			'expected'      => [
				'cleaned' => [
					'vfs://public/C:\\releases+ (site):\\wp-content/cache/busting/1/wp-content/test.js' => null,
				],
			],
			'wp_content_dir' => 'vfs://public/C:\\releases+ (site):\\wp-content',
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
