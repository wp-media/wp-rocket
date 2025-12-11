<?php

return [
	'testShouldLocalizeWhenUserHasCapabilityAndOptinEnabled' => [
		'config' => [
			'user_can_manage'   => true,
			'optin_enabled'     => true,
			'request_uri'       => '/wp-admin/options-general.php?page=wprocket',
			'consumer_email'    => '',
		],
		'expected' => [
			'should_localize' => true,
			'data' => [
				'optin_enabled' => true,
				'plugin'        => 'wp rocket 3.19',
				'brand'         => 'wp media',
				'app'           => 'wp rocket',
				'context'       => 'wp_plugin',
				'path'          => '/wp-admin/options-general.php?page=wprocket',
				'user_id'       => '',
			],
		],
	],
	'testShouldLocalizeWhenUserHasCapabilityAndOptinDisabled' => [
		'config' => [
			'user_can_manage'   => true,
			'optin_enabled'     => false,
			'request_uri'       => '/wp-admin/options-general.php?page=wprocket',
			'consumer_email'    => '',
		],
		'expected' => [
			'should_localize' => true,
			'data' => [
				'optin_enabled' => false,
				'plugin'        => 'wp rocket 3.19',
				'brand'         => 'wp media',
				'app'           => 'wp rocket',
				'context'       => 'wp_plugin',
				'path'          => '/wp-admin/options-general.php?page=wprocket',
				'user_id'       => '',
			],
		],
	],
	'testShouldLocalizeWithDashboardPath' => [
		'config' => [
			'user_can_manage'   => true,
			'optin_enabled'     => true,
			'request_uri'       => '/wp-admin/admin.php?page=wprocket',
			'consumer_email'    => '',
		],
		'expected' => [
			'should_localize' => true,
			'data' => [
				'optin_enabled' => true,
				'plugin'        => 'wp rocket 3.19',
				'brand'         => 'wp media',
				'app'           => 'wp rocket',
				'context'       => 'wp_plugin',
				'path'          => '/wp-admin/admin.php?page=wprocket',
				'user_id'       => '',
			],
		],
	],
	'testShouldNotLocalizeWhenUserLacksCapability' => [
		'config' => [
			'user_can_manage'   => false,
			'optin_enabled'     => true,
			'request_uri'       => '/wp-admin/options-general.php?page=wprocket',
			'consumer_email'    => '',
		],
		'expected' => [
			'should_localize' => false,
		],
	],
	'testShouldHandleMissingRequestUri' => [
		'config' => [
			'user_can_manage'   => true,
			'optin_enabled'     => true,
			'request_uri'       => null,
			'consumer_email'    => '',
		],
		'expected' => [
			'should_localize' => true,
			'data' => [
				'optin_enabled' => true,
				'plugin'        => 'wp rocket 3.19',
				'brand'         => 'wp media',
				'app'           => 'wp rocket',
				'context'       => 'wp_plugin',
				'path'          => '',
				'user_id'       => '',
			],
		],
	],
	'testShouldHashConsumerEmailWhenAvailable' => [
		'config' => [
			'user_can_manage'   => true,
			'optin_enabled'     => true,
			'request_uri'       => '/wp-admin/options-general.php?page=wprocket',
			'consumer_email'    => 'test@example.com',
		],
		'expected' => [
			'should_localize' => true,
			'data' => [
				'optin_enabled' => true,
				'plugin'        => 'wp rocket 3.19',
				'brand'         => 'wp media',
				'app'           => 'wp rocket',
				'context'       => 'wp_plugin',
				'path'          => '/wp-admin/options-general.php?page=wprocket',
				'user_id'       => hash( 'sha224', 'test@example.com' ),
			],
		],
	],
];
