<?php

declare( strict_types=1 );


return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'example.org' => [
					'wp-rocket' => [
						'index.html'         => '',
						'index.html_gzip'    => '',
						'.mobile-detect'     => '',
						'.no-webp'           => '',
						'do-not-remove.html' => '',

						// Pagination
						'page'               => [
							'2' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							]
						],

						'uncategorized' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],

						//language
						'fr'            => [
							'index.html'      => '',
							'index.html_gzip' => '',

							'page'          => [
								'2' => [
									'index.html'      => '',
									'index.html_gzip' => '',
								],
							],
							'uncategorized' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							]
						]
					]
				]
			]
		]
	],

	// Test data.
	'test_data' => [
		'shouldDeleteIndexesMobileDetectAndNoWebp'   => [
			'lang'     => [
				'',
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'             => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'        => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/.mobile-detect'         => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/.no-webp'               => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/page/2/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/page/2/index.html_gzip' => null,
				],
			],
		],

		'shouldDeleteLanguageIndexesWhenLangIsGiven' => [
			'lang'     => [
				'fr',
			],
			'expected' => [
				'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html'      => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/fr/index.html_gzip' => null,
				],
			],
		],
	],
];
