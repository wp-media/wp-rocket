<?php

$live_server_url = 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one';
$live_issuer_url = 'https://wp-rocket.me/wp-json/grnd/v1/token';

return [
	'key_missing'                    => [
		'config'   => [
			'consumer_key'   => '',
			'consumer_email' => 'user@example.org',
		],
		'expected' => [ 'is_empty' => true ],
	],
	'email_missing'                  => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => '',
		],
		'expected' => [ 'is_empty' => true ],
	],
	'support_chat_configuration'     => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'product'          => 'wprocket',
				'brand'            => 'wp-rocket',
				'i18n'             => [ 'columnLabel' => 'Ask Support' ],
				'page_context'     => 'support',
				'capability'       => 'rocket_manage_options',
				'grnd_license_key' => 'test-consumer-key|user@example.org',
				'terms_url'        => 'https://wp-rocket.me/terms/',
				'privacy_url'      => 'https://wp-rocket.me/privacy-policy/',
				'site_access_url'  => 'https://wp-rocket.me/privacy-policy/',
				'screens'          => [ 'settings_page_wprocket' ],
				'column'           => [
					'side'        => 'right',
					'startOnOpen' => true,
				],
				'column_start_on_open' => true,
				'show_agent_selector' => false,
			],
		],
	],
	'live_urls_by_default'           => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
			'constants'      => [],
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'server_url'      => $live_server_url,
				'grnd_issuer_url' => $live_issuer_url,
			],
		],
	],
	'constants_empty_fallback'       => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
			'constants'      => [
				'WP_ROCKET_WAP_SERVER_URL' => '',
				'WP_ROCKET_WAP_ISSUER_URL' => '',
			],
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'server_url'      => $live_server_url,
				'grnd_issuer_url' => $live_issuer_url,
			],
		],
	],
	'constants_set'                  => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
			'constants'      => [
				'WP_ROCKET_WAP_SERVER_URL' => 'https://wap.example.org',
				'WP_ROCKET_WAP_ISSUER_URL' => 'https://issuer.example.org/token',
			],
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'server_url'      => 'https://wap.example.org',
				'grnd_issuer_url' => 'https://issuer.example.org/token',
			],
		],
	],
	'always_support_agent'           => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'page_context' => 'support',
			],
		],
	],
	'permission_rows'                => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				// The WP Rocket row is a real choice, allowed by default.
				// The whole-site row only offers Deny and is locked, so other plugins' and core abilities never run.
				'permissions' => [
					[
						'id'           => 'manage-plugin',
						'label'        => 'Manage your WP Rocket plugin',
						'options'      => [
							[
								'value' => 'allow',
								'label' => 'Allow',
							],
							[
								'value' => 'ask',
								'label' => 'Ask permission',
							],
							[
								'value' => 'deny',
								'label' => 'Deny',
							],
						],
						'defaultValue' => 'allow',
					],
					[
						'id'           => 'manage-site',
						'label'        => 'Manage your WordPress site',
						'options'      => [
							[
								'value' => 'deny',
								'label' => 'Deny',
							],
						],
						'defaultValue' => 'deny',
						'disabled'     => true,
					],
				],
			],
		],
	],
	'user_cannot_use_assistant'      => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
			'can_use_ai'     => false,
		],
		'expected' => [ 'is_empty' => true ],
	],
	'invalid_key_skips_user_data'    => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
			'valid_key'      => false,
			'can_use_ai'     => null,
		],
		'expected' => [ 'is_empty' => true ],
	],
];
