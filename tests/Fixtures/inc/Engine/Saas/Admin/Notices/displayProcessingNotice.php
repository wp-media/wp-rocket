<?php

return [
	'test_data' => [
		'shouldDoNothingWhenNotWPRSettingsPage' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'dashboard',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => false,
				'saas_transient'         => false,
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
				'saas_transient'         => false,
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
				'saas_transient'         => false,
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
				'saas_transient'         => false,
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
				'saas_transient'         => false,
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
				'saas_transient'         => false,
			],
			'expected' => true,
		],
		'shouldShowNoNoticeWhenSaasError' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'remove_unused_css' => 1,
				'transient'         => time() + 3600,
				'saas_transient'         => true,
			],
			'expected' => false,
		],
	],
];
