<?php

return [
	'vfs_dir'   => 'wp-content/cache/min/',
	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'min' => [
					'1'         => [
						'combined1.css' => '',
						'combined2.css' => '',
					],
				],
			],
		],
	],
	// Default settings.
	'settings'  => [
		'minify_css'  => false,
		'exclude_css' => [],
		'cdn'         => false,
		'cdn_cnames'  => [],
	],
	'test_data' => [
		'shouldNotCleanMinify'             => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'should_run' => false,
		],
		'shouldNotCleanMinifyNewCname'     => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'should_run' => false,
		],
		'shouldCleanMinifyCSS'             => [
			'value'     => [
				'minify_css'  => true,
				'exclude_css' => [],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'should_run' => true,
		],
		'shouldCleanMinifyExcludeCSS'      => [
			'value'     => [
				'minify_css'  => true,
				'exclude_css' => [ '/wp-content/plugins/some-plugin/file.css' ],
				'cdn'         => false,
				'cdn_cnames'  => [],
			],
			'should_run' => true,
		],
		'shouldCleanMinifyCDN'             => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [],
			],
			'should_run' => true,
		],
		'shouldCleanMinifyCDNCname'        => [
			'value'     => [
				'minify_css'  => false,
				'exclude_css' => [],
				'cdn'         => true,
				'cdn_cnames'  => [ 'cname' ],
			],
			'should_run' => true,
		],
	],
];
