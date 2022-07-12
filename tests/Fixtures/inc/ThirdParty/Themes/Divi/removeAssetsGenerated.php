<?php
return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [

		'shouldDisableSettingWhenThemeDivi' => [
			'config'   => [
				'stylesheet' => 'divi',
				'theme-name' => 'Divi',
				'is-child'   => '',
				'set-lazy'   => 1,
			],
			'expected' => false,
		],

		'shouldDisableSettingWhenChildThemeDiviParent' => [
			'config'   => [
				'stylesheet'  => 'divi-child',
				'theme-name'  => 'Divi Child',
				'is-child'    => 'divi',
				'parent-name' => 'Divi',
				'set-lazy'    => 1,
			],
			'expected' => false,
		],
	],
];
