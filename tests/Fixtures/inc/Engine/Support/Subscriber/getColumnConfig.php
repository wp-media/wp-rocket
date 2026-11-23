<?php

$live_server_url = 'https://wap.wp-rocket.me';
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
				'i18n'             => [
					'columnLabel'       => 'Ask Support',
					'headerTitle'       => 'AI Assistant',
					'settings'          => 'Settings',
					'closeAssistant'    => 'Close',
					'consentText'       => 'Please review and accept the {terms} for the WP Rocket Assistant.',
					'consentAccessText' => 'The Assistant uses a safe {link}WordPress Application Password{/link} with limited permissions, which you can remove at any time.',
					'consentRequired'   => 'You need to accept the Terms & Conditions to use the AI Assistant. You can alternatively {contact}contact the support team{/contact} or access {docs}the documentation{/docs}.',
					'errorUnavailable'  => 'The AI Assistant is temporarily unavailable. Please try again in a few minutes. You can alternatively {contact}contact the support team{/contact} or access {docs}the documentation{/docs}.',
					'errorAuthFailed'   => 'The AI Assistant is temporarily unavailable. Please try again in a few minutes. You can alternatively {contact}contact the support team{/contact} or access {docs}the documentation{/docs}.',
				],
				'page_context'     => 'support',
				'capability'       => 'rocket_manage_options',
				'grnd_license_key' => 'test-consumer-key|user@example.org',
				'terms_url'        => 'https://wp-rocket.me/terms/',
				'privacy_url'      => 'https://wp-rocket.me/privacy-policy/',
				'site_access_url'  => 'https://docs.wp-rocket.me/article/1936-ai-assistant-access-and-limitations?utm_source=wp_plugin&utm_medium=wp_rocket',
				'contact_url'      => 'https://wp-rocket.me/contact/',
				'docs_url'         => 'https://docs.wp-rocket.me/?utm_source=wp_plugin&utm_medium=wp_rocket',
				'screens'          => [ 'settings_page_wprocket' ],
				'column'           => [
					'side'        => 'right',
					'startOnOpen' => true,
					'persist'     => false,
				],
				'column_start_on_open' => true,
				'column_persist'       => false,
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
	'grnd_sends_user_ref'            => [
		'config'   => [
			'consumer_key'   => 'test-consumer-key',
			'consumer_email' => 'user@example.org',
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'grnd_send_user_ref' => true,
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
				// Reading WP Rocket is a real choice, allowed by default.
				// Changing WP Rocket and the whole site only offer Deny and are locked, so no write ability runs yet.
				'permissions' => [
					[
						'id'           => 'read-plugin',
						'label'        => 'Read WP Rocket settings',
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
						'id'           => 'manage-plugin',
						'label'        => 'Change WP Rocket settings',
						'options'      => [
							[
								'value' => 'deny',
								'label' => 'Deny',
							],
						],
						'defaultValue' => 'deny',
						'disabled'     => true,
						'badge'        => 'Coming soon',
					],
					[
						'id'           => 'manage-site',
						'label'        => 'Read & Write WordPress configuration',
						'options'      => [
							[
								'value' => 'deny',
								'label' => 'Deny',
							],
						],
						'defaultValue' => 'deny',
						'disabled'     => true,
						'badge'        => 'Coming soon',
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
	'chat_enabled_by_filter'      => [
		'config'   => [
			'consumer_key'     => 'test-consumer-key',
			'consumer_email'   => 'user@example.org',
			'filter_available' => true,
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [
				'product'          => 'wprocket',
				'grnd_license_key' => 'test-consumer-key|user@example.org',
			],
		],
	],
	'chat_disabled_by_filter'     => [
		'config'   => [
			'consumer_key'     => 'test-consumer-key',
			'consumer_email'   => 'user@example.org',
			'filter_available' => false,
		],
		'expected' => [ 'is_empty' => true ],
	],
	'assistant_disabled_by_filter' => [
		'config'   => [
			'consumer_key'        => 'test-consumer-key',
			'consumer_email'      => 'user@example.org',
			'ai_filter_available' => false,
		],
		'expected' => [ 'is_empty' => true ],
	],
	'assistant_enabled_by_both_filters' => [
		'config'   => [
			'consumer_key'        => 'test-consumer-key',
			'consumer_email'      => 'user@example.org',
			'ai_filter_available' => true,
			'filter_available'    => true,
		],
		'expected' => [
			'is_empty' => false,
			'fields'   => [ 'product' => 'wprocket' ],
		],
	],
];
