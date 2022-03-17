<?php

return [
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
		],
		'expected' => [
			'message'     => '<strong>WP Rocket</strong>: Your homepage has been processed. WP Rocket will continue to generate Used CSS for up to 100 URLs per 60 second(s). We suggest enabling <a href="#preload">Sitemap Preload</a> for the fastest results.<br>To learn more about the process check our <a href="http://example.org" data-beacon-article="123" rel="noopener noreferrer" target="_blank">documentation</a>.',
			'dismissible' => 'hidden',
			'id'          => 'rocket-notice-rucss-success',
			'dismiss_button' => 'rucss_success_notice',
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
		],
		'expected' => [
			'message'     => '<strong>WP Rocket</strong>: Your homepage has been processed. WP Rocket will continue to generate Used CSS for up to 100 URLs per 60 second(s). We suggest enabling <a href="#preload">Sitemap Preload</a> for the fastest results.<br>To learn more about the process check our <a href="http://example.org" data-beacon-article="123" rel="noopener noreferrer" target="_blank">documentation</a>.',
			'dismissible' => '',
			'id'          => 'rocket-notice-rucss-success',
			'dismiss_button' => 'rucss_success_notice',
		],
	],
];
