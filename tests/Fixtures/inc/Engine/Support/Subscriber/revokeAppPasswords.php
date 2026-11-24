<?php
/**
 * Shared by the unit test, which only reads `expected.product`, and the integration test.
 *
 * Integration config:
 * - `min_library_version`: skips the case on an older bundled wap-client.
 * - `passwords`: Application Passwords to create, keyed by a label. A `product` makes it a chat password
 *   minted by the library under that name; without one it is created by the user, and `backdate` moves
 *   its creation two hours back.
 *
 * Integration expected:
 * - `names`: exact names, checked before deactivation.
 * - `kept` / `revoked`: labels of the passwords that must survive or be gone after deactivation.
 */

return [
	'revoke_wp_rocket_passwords'            => [
		'config'   => [
			'min_library_version' => '',
			'passwords'           => [
				'rocket' => [
					'product' => 'wprocket',
					'name'    => 'wp-rocket-chat',
				],
				'other'  => [
					'product' => 'imagify',
					'name'    => 'other-product-chat',
				],
				'user'   => [
					'name' => 'Created by the user',
				],
			],
		],
		'expected' => [
			'product' => 'wprocket',
			'names'   => [],
			'kept'    => [ 'other', 'user' ],
			'revoked' => [ 'rocket' ],
		],
	],
	'keep_users_own_passwords'              => [
		'config'   => [
			'min_library_version' => '3.1.0',
			'passwords'           => [
				'rocket' => [
					'product' => 'wprocket',
					'name'    => 'WP Rocket AI Assistant - 1h access to WP Rocket MCP abilities',
				],
				'own'    => [
					'name'     => 'WAP – my staging deploy key',
					'backdate' => true,
				],
				'plain'  => [
					'name'     => 'plain key',
					'backdate' => true,
				],
			],
		],
		'expected' => [
			'product' => 'wprocket',
			'names'   => [],
			'kept'    => [ 'own', 'plain' ],
			'revoked' => [ 'rocket' ],
		],
	],
	'name_chat_password_exactly_and_revoke' => [
		'config'   => [
			'min_library_version' => '3.1.0',
			'passwords'           => [
				'rocket' => [
					'product' => 'wprocket',
					'name'    => 'WP Rocket AI Assistant - 1h access to WP Rocket MCP abilities',
				],
				'other'  => [
					'product' => 'imagify',
					'name'    => 'Imagify Assistant',
				],
			],
		],
		'expected' => [
			'product' => 'wprocket',
			'names'   => [
				'rocket' => 'WP Rocket AI Assistant - 1h access to WP Rocket MCP abilities',
			],
			'kept'    => [ 'other' ],
			'revoked' => [ 'rocket' ],
		],
	],
];
