<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [

		'shouldNotDisableSettingWhenThemeNotDivi' => [
			'config'   => [
				'stylesheet' => 'twentytwenty',
				'theme-name' => 'Twenty Twenty',
				'is-child'   => '',
				'set-lazy'   => 0,
			],
			'expected' => [
				'settings' => [],
			],
		],

		'shouldNotDisableSettingWhenChildThemeNotDiviParent' => [
			'config'   => [
				'stylesheet'  => 'some-child-theme',
				'theme-name'  => 'Child of Twenty Twenty',
				'is-child'    => 'twentytwenty',
				'parent-name' => 'Twenty Twenty',
				'set-lazy'    => 0,
			],
			'expected' => [
				'settings' => [],
			],
		],

		'shouldDisableSettingWhenThemeDivi' => [
			'config'   => [
				'stylesheet' => 'divi',
				'theme-name' => 'Divi',
				'is-child'   => '',
				'set-lazy'   => 1,
			],
			'expected' => [
				'settings' => [
					'lazyload_youtube' => 0,
				],
			],
		],

		'shouldDisableSettingWhenChildThemeDiviParent' => [
			'config'   => [
				'stylesheet'  => 'divi-child',
				'theme-name'  => 'Divi Child',
				'is-child'    => 'divi',
				'parent-name' => 'Divi',
				'set-lazy'    => 1,
			],
			'expected' => [
				'settings' => [
					'lazyload_youtube' => 0,
				],
			],
		],
	],
];
