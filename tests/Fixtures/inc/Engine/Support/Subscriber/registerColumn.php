<?php

return [
	'wap_client_missing'           => [
		'config'   => [
			'wap_client_exists' => false,
		],
		'expected' => [
			'registered' => false,
		],
	],
	'without_licence_data'         => [
		'config'   => [
			'wap_client_exists' => true,
			'consumer_key'      => '',
			'consumer_email'    => '',
		],
		'expected' => [
			'registered' => false,
		],
	],
	'register_with_configuration'  => [
		'config'   => [
			'wap_client_exists' => true,
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
			'wap_client_exists' => true,
			'consumer_key'      => 'test-consumer-key',
			'consumer_email'    => 'user@example.org',
			'user_data'         => [ 'can_use_ai' => false ],
		],
		'expected' => [
			'registered' => false,
		],
	],
	'flag_missing_registers'       => [
		'config'   => [
			'wap_client_exists' => true,
			'consumer_key'      => 'test-consumer-key',
			'consumer_email'    => 'user@example.org',
			'user_data'         => [],
		],
		'expected' => [
			'registered' => true,
		],
	],
];
