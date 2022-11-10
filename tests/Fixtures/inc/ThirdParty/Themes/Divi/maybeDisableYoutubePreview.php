<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [

		'shouldNotDisableSettingWhenThemeNotDivi' => [
			'config'   => [
				'stylesheet' => 'twentytwenty',
				'template' => 'Twenty Twenty',
				'set-lazy'   => 0,
			],
			'expected' => [
				'settings' => [],
			],
		],

		'shouldNotDisableSettingWhenChildThemeNotDiviParent' => [
			'config'   => [
				'stylesheet'  => 'child-of-twentytwenty',
				'template'  => 'Child of Twenty Twenty',
				'set-lazy'    => 0,
			],
			'expected' => [
				'settings' => [],
			],
		],

		'shouldDisableSettingWhenThemeDivi' => [
			'config'   => [
				'stylesheet' => 'divi',
				'template' => 'Divi',
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
				'template'  => 'Divi',
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
