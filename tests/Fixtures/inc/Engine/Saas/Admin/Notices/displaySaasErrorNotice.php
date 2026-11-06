<?php
$beacon = [
	'en' => [
		'id'  => '6076083ff8c0ef2d98df1f97',
		'url' => 'https://docs.wp-rocket.me/article/1529-remove-unused-css?utm_source=wp_plugin&utm_medium=wp_rocket#basic-requirements',
	],
	'fr' => [
		'id'  => '60d499a705ff892e6bc2a89e',
		'url' => 'https://fr.docs.wp-rocket.me/article/1577-supprimer-les-ressources-css-inutilisees?utm_source=wp_plugin&utm_medium=wp_rocket#basic-requirements',
	],
];

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
		'notWritableShouldDisplayNothing' => [
			'config' => [
				'is_writable' => false,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => true,
				'boxes'             => [],
				'saas_transient'         => true,
				'beacon' => $beacon,
			],
			'expected' => ''
		],
		'noRightShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => false,
				'is_enabled' => true,
				'boxes'             => [],
				'saas_transient'         => true,
				'beacon' => $beacon,
			],
			'expected' => ''
		],
		'wrongScreenShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'wrong',
				],
				'has_rights' => true,
				'is_enabled' => true,
				'boxes'             => [],
				'saas_transient'         => true,
				'beacon' => $beacon,
			],
			'expected' => ''
		],
		'NotEnabledShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => false,
				'boxes'             => [],
				'saas_transient'         => true,
				'beacon' => $beacon,
			],
			'expected' => ''
		],
		'NotSassErrorShouldDisplayNothing' => [
			'config' => [
				'is_writable' => true,
				'current_screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_rights' => true,
				'is_enabled' => false,
				'boxes'             => [],
				'saas_transient'         => false,
				'beacon' => $beacon,
			],
			'expected' => ''
		]
	]
];
