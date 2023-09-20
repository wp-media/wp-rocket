<?php
return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
		'tableShouldDisplayNothing' => [
			'config' => [
				'is_writable' => false,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => true,
				'table_exists' => true,
			],
			'expected' => ''
		],
		'noRightShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => false,
				'is_enabled' => true,
				'table_exists' => false,
			],
			'expected' => ''
		],
		'wrongScreenShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'wrong',
				],
				'has_rights' => true,
				'is_enabled' => true,
				'table_exists' => false,
			],
			'expected' => ''
		],
		'notEnabledShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => false,
				'table_exists' => false,
			],
			'expected' => ''
		]
	]
];
