<?php
return [
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content' => [
			'cache'  => [
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
				'avada' => [
					'style.css' => '
					/**
					 * Theme Name: Avada
					 */',
					'index.php' => '',
				],
			],
		],
	],

	'test_data' => [
		// No options enabled.
		[
			'fusion_options' => [],
			'expected'       => [],
		],
		// LazyLoad disabled
		[
			'fusion_options' => [
				'lazy_load'  => 'not_avada',
			],
			'expected'       => [],
		],
		// LazyLoad enabled
		[
			'fusion_options' => [
				'lazy_load'  => 'avada',
			],
			'expected'       => [ 'Avada' ],
		],
	],
];
