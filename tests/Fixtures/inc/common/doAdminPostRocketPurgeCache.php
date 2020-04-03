<?php

return [

	'vfs_dir'   => 'wp-content/cache/wp-rocket/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html'      => '',
						'index.html_gzip' => '',
						'en'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'de'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'fr'              => [
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
		'purge'     => [
			[
				'$_GET'  => [
					'type'     => 'post-123',
					'_wpnonce' => 'whatever',
					'lang'     => 'en',
				],
				'config' => [
					'type'    => 'post',
					'post_id' => 123,
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
				'config' => [],
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
					'_wpnonce' => 'whatever',
				],
				'config' => [
					'current_user_can' => true,
					'type'    => 'post',
					'post_id' => 123,
				],
			],
		],
	],
];
