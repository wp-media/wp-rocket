<?php

return [
	'test_data' => [
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
