<?php
/**
 * Test Data for Cache Dynamic Resource.
 */

return [
	'vfs_dir'   => 'wp-content/cache/min/',
	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'min' => [
					'1'         => [
						'fa2965d41f1515951de523cecb81f85e.css' => '',
						'2n7x3vd41f1515951de523cecb81f85e.css' => '',
					],
					'3rd-party' => [
						'2n7x3vd41f1515951de523cecb81f85e.css' => '',
						'fa2965d41f1515951de523cecb81f85e.css' => '',
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
				'cdn'         => false,
				'cdn_cnames'  => [ 'cname' ],
			],
			'should_run' => true,
		],
	],
];
