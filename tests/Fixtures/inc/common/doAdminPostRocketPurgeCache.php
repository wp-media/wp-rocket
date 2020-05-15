<?php

return [

	'vfs_dir'   => 'wp-content/cache/wp-rocket/example.org/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html'        => '',
						'index.html_gzip'   => '',
						'lorem-ipsum-dolor' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'en'                => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'de'                => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'fr'                => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [

		'purge' => [
			[
				'$_GET'  => [
					'type'     => 'post-123',
					'_wpnonce' => 'post-123',
					'lang'     => 'en',
				],
				'config' => [
					'type'      => 'post',
					'post_id'   => 123, // Auto populated in integration tests.
					'lang'      => 'en',
					'file'      => 'lorem-ipsum-dolor',
					'post_data' => [
						'post_name'  => 'lorem-ipsum-dolor',
						'post_title' => 'Lorem ipsum dolor',
					],
				],
			],
			[
				'$_GET'  => [
					'type'     => 'all',
					'_wpnonce' => 'all',
					'lang'     => 'en',
				],
				'config' => [
					'type'    => 'all',
					'lang'    => 'en',
				],
			],
		],

		'wontpurge' => [
			[
				'$_GET'  => [
					'type'     => 'invalid',
					'_wpnonce' => '',
				],
				'config' => [
					'current_user_can' => true,
				],
			],
			[
				'$_GET'  => [
					'type'     => 'invalid',
					'_wpnonce' => '',
				],
				'config' => [
					'current_user_can' => false,
				],
			],
			[
				'$_GET'  => [
					'type'     => 'invalid',
					'_wpnonce' => 'invalid',
				],
				'config' => [
					'current_user_can' => true,
				],
			],
		],
	],
];
