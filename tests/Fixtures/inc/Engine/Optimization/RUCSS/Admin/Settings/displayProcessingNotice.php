<?php

return [
	'shouldDoNothingWhenNotWPRSettingsPage' => [
		'config' => [
			'current_screen'    => (object) [
				'id' => 'dashboard',
			],
			'capability'        => true,
			'remove_unused_css' => 1,
			'transient'         => false,
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
		],
		'expected' => true,
	],
];
