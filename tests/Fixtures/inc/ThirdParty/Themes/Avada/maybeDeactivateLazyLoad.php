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
		// Empty old value.
		[
			'old'      => [],
			'value'    => [],
			'expected' => [
				'lazyload' => 0,
			],
		],
		// Not empty old value.
		[
			'old'      => [
				'lazy_load' => 1,
			],
			'value'    => [],
			'expected' => [
			],
		],
		// Not empty old value, but no avada.
		[
			'old'      => [
				'lazy_load' => 1,
			],
			'value'    => [
				'lazy_load' => 'not_avada',
			],
			'expected' => [
			],
		],
		// Not empty old value, but avada.
		[
			'old'      => [
				'lazy_load' => 1,
			],
			'value'    => [
				'lazy_load' => 'avada',
			],
			'expected' => [
				'lazyload' => 0,
			],
		],
	],
];
