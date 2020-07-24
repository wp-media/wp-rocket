<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [

		'shouldNotDisableSettingWhenThemeNotDivi' => [
			'config'   => [
				'stylesheet' => 'Any Theme but Divi',
				'template'   => '',
				'set-lazy'   => 0,
			],
			'expected' => [
				'settings' => [],
			],
		],

		'shouldDisableSettingWhenThemeDivi' => [
			'config'   => [
				'stylesheet' => 'Divi',
				'template'   => '',
				'set-lazy'   => 1,
			],
			'expected' => [
				'settings' => [
					'lazyload_youtube' => 0,
				],
			],
		],

		'shouldDisableSettingWhenThemeDiviChild' => [
			'config'   => [
				// Skip for integration test until we have a WP_Theme integration framework.
				'int-skip'   => true,
				'stylesheet' => 'Divi Child',
				'template'   => 'Divi',
				'set-lazy'   => 1,
			],
			'expected' => [
				'settings' => [
					'lazyload_youtube' => 0,
				],
			],
		],
	],
];
