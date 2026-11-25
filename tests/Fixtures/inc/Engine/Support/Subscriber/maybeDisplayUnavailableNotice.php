<?php

return [
	'without_capability'        => [
		'config'   => [
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
	'multisite_not_network_activated' => [
		'config'   => [
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'unavailable_reason'      => 'multisite',
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
		],
		'expected' => [
			'notice_called'    => true,
			'status'           => 'warning',
			'message_contains' => 'used in a multisite network',
		],
	],
	'reason_https' => [
		'config'   => [
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'unavailable_reason'      => 'https',
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
	'reason_disabled' => [
		'config'   => [
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'unavailable_reason'      => 'disabled',
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
	'reason_empty_is_silent' => [
		'config'   => [
			'wap_check_called'        => true,
			'app_passwords_available' => true,
			'unavailable_reason'      => '',
			'screen_id'               => 'settings_page_wprocket',
			'consumer_key'            => 'test-consumer-key',
			'consumer_email'          => 'user@example.org',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'reason_empty_while_unavailable' => [
		'config'   => [
			'wap_check_called'        => true,
			'app_passwords_available' => false,
			'unavailable_reason'      => '',
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
			'wap_check_called'        => true,
			'app_passwords_available' => false,
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
