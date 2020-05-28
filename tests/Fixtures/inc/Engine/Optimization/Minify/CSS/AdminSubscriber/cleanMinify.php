<?php

return [
	'vfs_dir'   => 'wp-content/cache/min/',

	// Default settings.
	'settings'  => [
		'minify_css'  => false,
		'exclude_css' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],

	'test_data' => [
		'shouldNotCleanMinify'             => [
			'settings'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'expected'   => [
				'cleaned' => [],
			],
		],
		'shouldNotCleanMinifyNewCname'     => [
			'settings'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'expected'   => [
				'cleaned' => [],
			],
		],
		'shouldCleanMinifyCSS'             => [
			'settings'     => [
				'minify_css'  => true,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
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
		'shouldCleanMinifyExcludeCSS'      => [
			'settings'     => [
				'minify_css'  => true,
				'exclude_css' => [ '/wp-content/plugins/some-plugin/file.css' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
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
		'shouldCleanMinifyCDN'             => [
			'settings'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
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
		'shouldCleanMinifyCDNCname'        => [
			'settings'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [ 'cname' ],
			],
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
	],
];
