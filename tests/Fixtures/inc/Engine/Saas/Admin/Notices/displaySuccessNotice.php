<?php

return [
	'test_data' => [
		'shouldDoNothingWhenNotWPRSettingsPage' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'dashboard',
				],
				'capability'        => true,
				'boxes'             => [],
				'manual_preload'    => 1,
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
				'boxes'             => [],
				'manual_preload'    => 1,
				'transient'         => false,
				'saas_transient'         => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenSaasError' => [
			'config' => [
				'current_screen'    => (object) [
					'id' => 'settings_page_wprocket',
				],
				'capability'        => true,
				'boxes'             => [],
				'manual_preload'    => 0,
				'transient'         => false,
				'saas_transient'         => true,
			],
			'expected' => false,
		],
	],
];
