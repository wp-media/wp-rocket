<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Virtual filesystem structure.
	'structure' => require WP_ROCKET_TESTS_FIXTURES_DIR . '/vfs-structure/default.php',

	// Test data.
	'test_data' => [
		'shouldBailOutWhenActionNotUpdate' => [
			'hook_extra' => [
				'action' => 'install',
			],
			'expected'   => [
				'cleaned'     => [],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,
					'vfs://public/wp-content/cache/wp-rocket/'    => true,
				],
			],
		],
		'shouldBailOutWhenTypeNotTheme'    => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'plugin',
			],
			'expected'   => [
				'cleaned'     => [],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,
					'vfs://public/wp-content/cache/wp-rocket/'    => true,
				],
			],
		],
		'shouldBailOutWhenThemesNotArray'  => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'plugin',
				'themes' => '',
			],
			'expected'   => [
				'cleaned'     => [],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/'          => true,
					'vfs://public/wp-content/cache/busting/'      => true,
					'vfs://public/wp-content/cache/critical-css/' => true,
					'vfs://public/wp-content/cache/wp-rocket/'    => true,
				],
			],
		],
		'shouldCleanDomain' => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'theme',
				'themes' => [ 'default' ],
			],
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/wp-rocket/example.org'                => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456' => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654'  => null,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org'           => [],
				],
				'non_cleaned' => [
					// fs entry => should scan the directory and get the file listings.
					'vfs://public/wp-content/cache/min/'                        => true,
					'vfs://public/wp-content/cache/busting/'                    => true,
					'vfs://public/wp-content/cache/critical-css/'               => true,
					'vfs://public/wp-content/cache/wp-rocket/'                  => false,
					'vfs://public/wp-content/cache/wp-rocket/index.html'        => false,
					'vfs://public/wp-content/cache/wp-rocket/dots.example.org/' => false,
				],
			],
		],
	],
];
