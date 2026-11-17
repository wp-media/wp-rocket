<?php

return [
	'without_licence_data'         => [
		'config'   => [
			'consumer_key'      => '',
			'consumer_email'    => '',
		],
		'expected' => [
			'registered' => false,
		],
	],
	'register_with_configuration'  => [
		'config'   => [
			'consumer_key'      => 'test-consumer-key',
			'consumer_email'    => 'user@example.org',
		],
		'expected' => [
			'registered'   => true,
			'config_check' => [
				'product'          => 'wprocket',
				'page_context'     => 'support',
				'grnd_license_key' => 'test-consumer-key|user@example.org',
				'screens'          => [ 'settings_page_wprocket' ],
			],
		],
	],
	'user_cannot_use_assistant'    => [
		'config'   => [
			'consumer_key'      => 'test-consumer-key',
			'consumer_email'    => 'user@example.org',
			'can_use_ai'        => false,
		],
		'expected' => [
			'registered' => false,
		],
	],
	'chat_enabled_by_filter'       => [
		'config'   => [
			'consumer_key'      => 'test-consumer-key',
			'consumer_email'    => 'user@example.org',
			'filter_available'  => true,
		],
		'expected' => [
			'registered'   => true,
			'config_check' => [
				'product'          => 'wprocket',
				'grnd_license_key' => 'test-consumer-key|user@example.org',
			],
		],
	],
	'chat_disabled_by_filter'      => [
		'config'   => [
			'consumer_key'      => 'test-consumer-key',
			'consumer_email'    => 'user@example.org',
			'filter_available'  => false,
		],
		'expected' => [
			'registered' => false,
		],
	],
];
