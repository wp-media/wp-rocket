<?php

$structure = require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php';
$structure['releases+ (site):'] = [
	'wp-content' => [
		'cache' => [
			'min' => [
				'1' => [ '5c795b0e3a1884eec34a989485f863ff.js' => '' ],
			],
		],
	],
];
$structure['C:\\releases+ (site):\\wp-content'] = [
	'cache' => [
		'min' => [
			'1' => [ '5c795b0e3a1884eec34a989485f863ff.js' => '' ],
		],
	],
];

return [
	'structure' => $structure,
	'vfs_dir'   => 'wp-content/cache/min/',

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
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/css/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/css/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
				],
			],
		],
		'shouldClean_css.gz'                      => [
			'extensions' => 'css.gz',
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
				],
			],
		],
		'shouldClean_js'                          => [
			'extensions' => 'js',
			'expected'   => [
				'cleaned'      => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/'  => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/'                        => [],

					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
				],
			],
		],
		'shouldClean_js_fromTimestampedPath'      => [
			'extensions'    => 'js',
			'expected'      => [
				'cleaned' => [
					'vfs://public/releases+ (site):/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' => null,
				],
			],
			'wp_content_dir' => 'vfs://public/releases+ (site):/wp-content',
		],
		'shouldClean_js_fromWindowsStylePath'     => [
			'extensions'    => 'js',
			'expected'      => [
				'cleaned' => [
					'vfs://public/C:\\releases+ (site):\\wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' => null,
				],
			],
			'wp_content_dir' => 'vfs://public/C:\\releases+ (site):\\wp-content',
		],
		'shouldClean_js.gz'                       => [
			'extensions' => 'js.gz',
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,

					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
				],
			],
		],
		'shouldCleanCssAndJs'                     => [
			'extensions' => [ 'css', 'js' ],
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'     => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz'  => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/css/'  => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/'   => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/css/'                        => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/'                         => [],

					'vfs://public/wp-content/cache/min/3rd-party/' => [],
				],
			],
		],
		'shouldClean_.gz'                         => [
			'extensions' => 'gz',
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz'         => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz'          => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz'  => null,
				],
			],
		],
	],
];
