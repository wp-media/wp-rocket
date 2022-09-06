<?php
return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
		'notWritableShouldDisplayNothing' => [
			'config' => [
				'is_writable' => false,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => true,
				'boxes'             => [],
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
				'boxes'             => [],
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
				'boxes'             => [],
			],
			'expected' => ''
		],
		'NotEnabledShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => false,
				'boxes'             => [],
			],
			'expected' => ''
		]
	]
];
