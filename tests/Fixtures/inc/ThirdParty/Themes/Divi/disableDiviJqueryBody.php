<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [
		'shouldDisableDiviJqueryBody' => [
			'config'   => [
				'stylesheet'  => 'divi',
				'theme-name'  => 'Divi',
			],
			'expected' => [
				'filter_priority' => 10
			],
		],
	],
];
