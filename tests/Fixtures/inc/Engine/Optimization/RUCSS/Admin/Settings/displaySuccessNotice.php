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
				'boxes'             => [],
				'remove_unused_css' => 1,
				'manual_preload'    => 1,
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
				'boxes'             => [],
				'remove_unused_css' => 1,
				'manual_preload'    => 1,
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
				'boxes'             => [],
				'remove_unused_css' => 0,
				'manual_preload'    => 1,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenNoticeDismissed' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'boxes'             => [
					'rucss_success_notice',
				],
				'remove_unused_css' => 1,
				'manual_preload'    => 1,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => false,
		],
		'shouldHideNoticeWhenTransient' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'boxes'             => [],
				'remove_unused_css' => 1,
				'manual_preload'    => 0,
				'transient'         => time(),
				'exists'         => true,
			],
			'expected' => [
				'message'     => '<strong>WP Rocket</strong>: The Used CSS of your homepage has been processed. WP Rocket will continue to generate Used CSS for up to 100 URLs per 60 second(s). We suggest enabling <a href="#preload">Preload</a> for the fastest results.<br>To learn more about the process check our <a href="http://example.org" data-beacon-article="123" rel="noopener noreferrer" target="_blank">documentation</a>.',
				'dismissible' => 'hidden',
				'id'          => 'rocket-notice-rucss-success',
				'dismiss_button' => 'rucss_success_notice',
				'dismiss_button_class' => 'button-primary',
			],
		],
		'shouldShowNoticeWhenNoTransient' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'boxes'             => [],
				'remove_unused_css' => 1,
				'manual_preload'    => 0,
				'transient'         => false,
				'exists'         => true,
			],
			'expected' => [
				'message'     => '<strong>WP Rocket</strong>: The Used CSS of your homepage has been processed. WP Rocket will continue to generate Used CSS for up to 100 URLs per 60 second(s). We suggest enabling <a href="#preload">Preload</a> for the fastest results.<br>To learn more about the process check our <a href="http://example.org" data-beacon-article="123" rel="noopener noreferrer" target="_blank">documentation</a>.',
				'dismissible' => '',
				'id'          => 'rocket-notice-rucss-success',
				'dismiss_button' => 'rucss_success_notice',
				'dismiss_button_class' => 'button-primary',
			],
		],
		'shouldDoNothingWhenNoTable' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'boxes'             => [],
				'remove_unused_css' => 1,
				'manual_preload'    => 0,
				'transient'         => false,
				'exists'         => false,
			],
			'expected' => false,
		],
	],
];
