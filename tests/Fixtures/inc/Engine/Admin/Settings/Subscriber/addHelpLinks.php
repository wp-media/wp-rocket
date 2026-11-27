<?php

return [
	'shouldAddSupportAndDocumentationToTheHelpGroup' => [
		'config'   => [
			'chat_registered' => false,
			'navigation'      => [],
		],
		'expected' => [
			'support'       => [
				'id'               => 'support',
				'title'            => 'Support',
				'menu_description' => 'Get help from our team',
				'group'            => 'help',
				'url'              => 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'documentation' => [
				'id'               => 'documentation',
				'title'            => 'Documentation',
				'menu_description' => 'Read the documentation',
				'group'            => 'help',
				'url'              => 'https://docs.wp-rocket.me/',
				'target'           => '_blank',
			],
		],
	],
	'shouldKeepExistingItems'                        => [
		'config'   => [
			'chat_registered' => false,
			'navigation'      => [
				'dashboard' => [
					'id'    => 'dashboard',
					'title' => 'Dashboard',
				],
			],
		],
		'expected' => [
			'dashboard'     => [
				'id'    => 'dashboard',
				'title' => 'Dashboard',
			],
			'support'       => [
				'id'               => 'support',
				'title'            => 'Support',
				'menu_description' => 'Get help from our team',
				'group'            => 'help',
				'url'              => 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'documentation' => [
				'id'               => 'documentation',
				'title'            => 'Documentation',
				'menu_description' => 'Read the documentation',
				'group'            => 'help',
				'url'              => 'https://docs.wp-rocket.me/',
				'target'           => '_blank',
			],
		],
	],
	'shouldOpenTheChatFromSupportWhenTheChatIsRegistered' => [
		'config'   => [
			'chat_registered' => true,
			'navigation'      => [],
		],
		'expected' => [
			'support'       => [
				'id'               => 'support',
				'title'            => 'Support',
				'menu_description' => 'Get help from our team',
				'group'            => 'help',
				'url'              => 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket',
				'open_chat'        => true,
			],
			'documentation' => [
				'id'               => 'documentation',
				'title'            => 'Documentation',
				'menu_description' => 'Read the documentation',
				'group'            => 'help',
				'url'              => 'https://docs.wp-rocket.me/',
				'target'           => '_blank',
			],
		],
	],
];
