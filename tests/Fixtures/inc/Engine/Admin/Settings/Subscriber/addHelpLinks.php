<?php

return [
	'shouldAddSupportAndDocumentationToTheHelpGroup' => [
		'config'   => [
			'navigation' => [],
		],
		'expected' => [
			'support'       => [
				'id'               => 'support',
				'title'            => 'Support',
				'menu_description' => 'Get help from our team',
				'group'            => 'help',
				'url'              => 'https://wp-rocket.me/support/?utm_source=wp_plugin&utm_medium=wp_rocket',
				'target'           => '_blank',
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
			'navigation' => [
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
				'url'              => 'https://wp-rocket.me/support/?utm_source=wp_plugin&utm_medium=wp_rocket',
				'target'           => '_blank',
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
