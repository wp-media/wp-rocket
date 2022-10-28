<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
		'shouldDoNothingWhenNotWPRSettingsPage' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'dashboard',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenNoCapability' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => false,
				'remove_unused_css' => 1,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenRUCSSDisabled' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'remove_unused_css' => 0,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenTransientTimeLessThanCurrentTime' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => time() - 60,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenNoTransient' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldShowNoticeWhenTransient' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => time() + 3600,
				'exists'         => true,
			],
			'expected' => true,
		],
		'shouldShowNoNoticeWhenNoTable' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => time() + 3600,
				'exists'         => false,
			],
			'expected' => false,
		],
	],
];
