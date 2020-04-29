<?php

return [
	'vfs_dir'   => 'public/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'min' => [
					'1' => [],
				],
			],
		],
	],
	'test_data' => [
		'option_disabled' => [
			'option_value'    => true,
			'dir_size_excess' => false,
		],
		'option_enabled_dir_size_ok' => [
			'option_value'    => false,
			'dir_size_excess' => false,
		],
		'option_enabled_dir_size_not_ok' => [
			'option_value'    => false,
			'dir_size_excess' => true,
		],
	]
];
