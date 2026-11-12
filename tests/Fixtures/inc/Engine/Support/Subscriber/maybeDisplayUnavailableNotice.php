<?php

return [
	'wap_client_missing'        => [
		'config'   => [
			'wap_class_exists' => false,
			'screen_id'        => 'settings_page_wprocket',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'without_capability'        => [
		'config'   => [
			'wap_class_exists' => true,
			'wap_check_called' => false,
			'screen_id'        => 'settings_page_wprocket',
			'consumer_key'     => 'test-consumer-key',
			'consumer_email'   => 'user@example.org',
			'current_user_can' => false,
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'without_screen'            => [
		'config'   => [
			'wap_class_exists' => true,
			'wap_check_called' => false,
			'screen_id'        => null,
			'consumer_key'     => 'test-consumer-key',
			'consumer_email'   => 'user@example.org',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'another_screen'            => [
		'config'   => [
			'wap_class_exists' => true,
			'wap_check_called' => false,
			'screen_id'        => 'dashboard',
			'consumer_key'     => 'test-consumer-key',
			'consumer_email'   => 'user@example.org',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'without_licence_data'      => [
		'config'   => [
			'wap_class_exists' => true,
			'wap_check_called' => false,
			'screen_id'        => 'settings_page_wprocket',
			'consumer_key'     => '',
			'consumer_email'   => '',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'app_passwords_available'   => [
		'config'   => [
			'wap_class_exists'        => true,
			'wap_check_called'        => true,
			'app_passwords_available' => true,
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'user_cannot_use_assistant' => [
		'config'   => [
			'wap_class_exists' => true,
			'wap_check_called' => false,
			'screen_id'        => 'settings_page_wprocket',
			'consumer_key'     => 'test-consumer-key',
			'consumer_email'   => 'user@example.org',
			'can_use_ai'       => false,
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'https_missing'             => [
		'config'   => [
			'wap_class_exists'        => true,
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'https_missing'           => true,
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
		],
		'expected' => [
			'notice_called'    => true,
			'status'           => 'warning',
			'message_contains' => 'requires HTTPS',
		],
	],
	'app_passwords_disabled'    => [
		'config'   => [
			'wap_class_exists'        => true,
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'https_missing'           => false,
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
		],
		'expected' => [
			'notice_called'    => true,
			'status'           => 'warning',
			'message_contains' => 'have been disabled',
		],
	],
	'chat_enabled_by_filter'    => [
		'config'   => [
			'wap_class_exists'        => true,
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'https_missing'           => false,
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
			'filter_available'        => true,
		],
		'expected' => [
			'notice_called'    => true,
			'status'           => 'warning',
			'message_contains' => 'have been disabled',
		],
	],
	'chat_disabled_by_filter'   => [
		'config'   => [
			'wap_class_exists'        => true,
			'wap_check_called'        => false,
			'app_passwords_available' => false,
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
			'filter_available'        => false,
		],
		'expected' => [
			'notice_called' => false,
		],
	],
];
