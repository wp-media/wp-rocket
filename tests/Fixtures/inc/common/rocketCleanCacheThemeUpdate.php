<?php

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir'   => 'wp-content/cache/',

	// Test data.
	'test_data' => [
		'shouldBailOutWhenActionNotUpdate' => [
			'hook_extra' => [
				'action' => 'install',
			],
			'expected'   => [
				'cleaned'      => [],
				'wp_get_theme' => null,
			],
		],
		'shouldBailOutWhenTypeNotTheme'    => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'plugin',
			],
			'expected'   => [
				'cleaned'      => [],
				'wp_get_theme' => null,
			],
		],
		'shouldBailOutWhenThemesNotArray'  => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'plugin',
				'themes' => '',
			],
			'expected'   => [
				'cleaned'      => [],
				'wp_get_theme' => null,
			],
		],
		'shouldBailOutWhenImporting'  => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'plugin',
				'themes' => [ 'default' ],
				'importing' => true,
			],
			'expected'   => [
				'cleaned'      => [],
				'wp_get_theme' => null,
			],
		],
		'shouldCleanDomain'                => [
			'hook_extra' => [
				'action' => 'update',
				'type'   => 'theme',
				'themes' => [ 'default' ],
			],
			'expected'   => [
				'cleaned'      => [],
				'wp_get_theme' => true,
			],
		],
	],
];
