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
	'shouldHideNoticeWhenTransient' => [
		'config' => [
			'current_screen'    => (object) [
				'id' => 'settings_page_wprocket',
			],
			'capability'        => true,
			'remove_unused_css' => 1,
			'transient'         => time(),
		],
		'expected' => [
			'message'     => 'Your homepage has been processed. WP Rocket will continue to generate Used CSS for up to 25 URLs per 60 second(s). We suggest enabling Preload for the fastest results. To learn more about the process check our documentation.',
			'dismissible' => 'is-dismissible hidden',
			'id'          => 'rocket-notice-rucss-success',
		],
	],
	'shouldShowNoticeWhenNoTransient' => [
		'config' => [
			'current_screen'    => (object) [
				'id' => 'settings_page_wprocket',
			],
			'capability'        => true,
			'remove_unused_css' => 1,
			'transient'         => false,
		],
		'expected' => [
			'message'     => 'Your homepage has been processed. WP Rocket will continue to generate Used CSS for up to 25 URLs per 60 second(s). We suggest enabling Preload for the fastest results. To learn more about the process check our documentation.',
			'dismissible' => 'is-dismissible',
			'id'          => 'rocket-notice-rucss-success',
		],
	],
];
