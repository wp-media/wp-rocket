<?php
return [
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content' => [
			'cache'  => [
				'min'       => [
					'1' => [
						'5c795b0e3a1884eec34a989485f863ff.js'  => '',
						'fa2965d41f1515951de523cecb81f85e.css' => '',
					],
				],
				'wp-rocket' => [
					'example.org'                                => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'about' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
				],
			],
			'themes' => [
				'bridge' => [
					'style.css' => '
					/**
					 * Theme Name: Bridge
					 */',
					'index.php' => '',
				],
			],
		],
	],

	'test_data' => [
		// No options enabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'settings'  => [
				'minify_css' => 0,
				'minify_js'  => 0,
			],
			'expected'  => true,
		],
		// Minify CSS option enabled, custom CSS & SVG disabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'settings'  => [
				'minify_css' => 1,
				'minify_js'  => 0,
			],
			'expected'  => true,
		],
		// Minify JS option enabled, custom JS disabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'settings'  => [
				'minify_css' => 0,
				'minify_js'  => 1,
			],
			'expected'  => true,
		],
		// Custom CSS options enabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 1,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'settings'  => [
				'minify_css' => 1,
				'minify_js'  => 0,
			],
			'expected'  => false,
		],
		// Custom SVG CSS options enabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 1,
				'custom_js'      => 0,
			],
			'settings'  => [
				'minify_css' => 1,
				'minify_js'  => 0,
			],
			'expected'  => false,
		],
		// All CSS options enabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 1,
				'custom_svg_css' => 1,
				'custom_js'      => 0,
			],
			'settings'  => [
				'minify_css' => 1,
				'minify_js'  => 0,
			],
			'expected'  => false,
		],
		// All JS options enabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 1,
			],
			'settings'  => [
				'minify_css' => 1,
				'minify_js'  => 1,
			],
			'expected'  => false,
		],
		// All options enabled.
		[
			'old_value' => [
				'custom_css'     => 0,
				'custom_svg_css' => 0,
				'custom_js'      => 0,
			],
			'new_value' => [
				'custom_css'     => 1,
				'custom_svg_css' => 1,
				'custom_js'      => 1,
			],
			'settings'  => [
				'minify_css' => 1,
				'minify_js'  => 1,
			],
			'expected'  => false,
		],
	],
];
