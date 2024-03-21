<?php

return [
	'vfs_dir'   => 'wp-content/cache/min/',

	// Default settings.
	'settings'  => [
		'minify_js'  => false,
		'exclude_js' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],

	'test_data' => [
		'shouldNotCleanMinify'             => [
			'settings'     => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'cleaned' => [],
			],
		],
		'shouldNotCleanMinifyNewCname'     => [
			'settings'     => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'cleaned' => [],
			],
		],
		'shouldCleanMinifyJS'             => [
			'settings'     => [
				'minify_js'  => true,
				'exclude_js' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/js/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/js/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js.gz' => null,
				],
			],
		],
		'shouldCleanMinifyExcludeJS'      => [
			'settings'     => [
				'minify_js'  => true,
				'exclude_js' => [ '/wp-content/plugins/some-plugin/file.js' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/js/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/js/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js.gz' => null,
				],
			],
		],
		'shouldCleanMinifyCDN'             => [
			'settings'     => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/js/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/js/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js.gz' => null,
				],
			],
		],
		'shouldCleanMinifyCDNCname'        => [
			'settings'     => [
				'minify_js'  => false,
				'exclude_js' => [],
				'cdn'         => true,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/js/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/js/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js.gz' => null,
				],
			],
		],
	],
];
